import type { SupabaseClient, User } from "@supabase/supabase-js";
import { sortByNewest } from "@/lib/format";
import { siteUrl } from "@/lib/supabase/env";
import type {
  AppNotification,
  CampfirePost,
  CampfireKind,
  CampfireSection,
  Comment,
  Membership,
  OutTherePost,
  Partnership,
  Profile,
  Reaction,
  ReactionKind,
  SeedStatus,
  SkillConnection,
  SkillOffer,
  SkillRequest,
  TargetType,
  UserSeed,
  World,
  WouldAgain,
} from "@/lib/types";

type Row = Record<string, unknown>;

function asString(value: unknown, fallback = "") {
  return typeof value === "string" ? value : fallback;
}

function asArray(value: unknown) {
  return Array.isArray(value) ? value.filter((item): item is string => typeof item === "string") : [];
}

function asCheckIns(value: unknown) {
  if (!Array.isArray(value)) return [];
  return value.flatMap((item) => {
    if (!item || typeof item !== "object") return [];
    const row = item as Row;
    const note = asString(row.note);
    if (!note) return [];
    return [
      {
        id: asString(row.id),
        at: asString(row.at),
        note,
        userId: asString(row.userId) || undefined,
      },
    ];
  });
}

export function profileFromRow(row: Row, interests: string[] = []): Profile {
  return {
    id: asString(row.id),
    email: asString(row.email),
    displayName: asString(row.display_name),
    bio: asString(row.bio),
    location: asString(row.location),
    avatarUrl: asString(row.avatar_url),
    intentions: asArray(row.intentions),
    interests,
    onboardingComplete: Boolean(row.onboarding_complete),
    emailVerified: Boolean(row.email_verified),
    showLocation: row.show_location !== false,
    showWaypoints: row.show_waypoints !== false,
    notifyReplies: row.notify_replies !== false,
    notifyWaypoints: Boolean(row.notify_waypoints),
    notifyCheckins: row.notify_checkins !== false,
    createdAt: asString(row.created_at, new Date().toISOString()),
    role: row.role === "admin" ? "admin" : "member",
    status: row.status === "suspended" || row.status === "deactivated" ? row.status : "active",
    growing: [],
    helpGrowing: [],
    helpPlant: [],
    helpPlantNote: "",
    supportWith: [],
    checkInFrequency: "",
    checkInStyle: "",
  };
}

function friendlyError(error: { message: string } | null) {
  if (!error) return null;
  const message = error.message.toLowerCase();
  if (message.includes("invalid login") || message.includes("invalid credentials")) {
    return "That email and password do not match.";
  }
  if (message.includes("already registered") || message.includes("already been registered")) {
    return "An account with that email already exists. Try logging in.";
  }
  if (message.includes("email not confirmed")) {
    return "Confirm your email first, then come back.";
  }
  if (message.includes("password")) {
    return "Use at least 8 characters.";
  }
  return "Something got in the way. Please try again.";
}

export async function loadWorld(supabase: SupabaseClient, authUser: User | null): Promise<World> {
  if (!authUser) {
    return {
      profiles: [],
      userSeeds: [],
      memberships: [],
      partnerships: [],
      offers: [],
      requests: [],
      connections: [],
      stories: [],
      campfire: [],
      comments: [],
      reactions: [],
      notifications: [],
    };
  }

  const [
    profiles,
    interests,
    memberships,
    userSeeds,
    partnerships,
    offers,
    requests,
    connections,
    stories,
    campfire,
    comments,
    reactions,
    notifications,
  ] = await Promise.all([
    supabase.from("profiles").select("*"),
    supabase.from("user_interests").select("user_id, interest_slug"),
    supabase.from("user_waypoints").select("*"),
    supabase.from("user_seeds").select("*"),
    supabase.from("same_partnerships").select("*"),
    supabase.from("skill_offers").select("*"),
    supabase.from("skill_requests").select("*"),
    supabase.from("skill_connections").select("*"),
    supabase.from("out_there_posts").select("*"),
    supabase.from("campfire_posts").select("*"),
    supabase.from("comments").select("*"),
    supabase.from("reactions").select("*"),
    supabase.from("notifications").select("*").eq("user_id", authUser.id),
  ]);

  const firstError = [
    profiles,
    interests,
    memberships,
    userSeeds,
    partnerships,
    offers,
    requests,
    connections,
    stories,
    campfire,
    comments,
    reactions,
    notifications,
  ].find((result) => result.error);

  if (firstError?.error) {
    throw new Error(firstError.error.message);
  }

  const interestMap = new Map<string, string[]>();
  for (const row of (interests.data ?? []) as Row[]) {
    const userId = asString(row.user_id);
    const slug = asString(row.interest_slug);
    interestMap.set(userId, [...(interestMap.get(userId) ?? []), slug]);
  }

  const profileRows = ((profiles.data ?? []) as Row[]).map((row) =>
    profileFromRow(row, interestMap.get(asString(row.id)) ?? []),
  );

  if (authUser.email_confirmed_at) {
    const mine = profileRows.find((profile) => profile.id === authUser.id);
    if (mine && !mine.emailVerified) {
      mine.emailVerified = true;
      await supabase.from("profiles").update({ email_verified: true }).eq("id", authUser.id);
    }
  }

  return {
    profiles: profileRows,
    memberships: ((memberships.data ?? []) as Row[]).map(
      (row): Membership => ({
        userId: asString(row.user_id),
        waypointId: asString(row.waypoint_id),
        joinedAt: asString(row.joined_at),
      }),
    ),
    userSeeds: ((userSeeds.data ?? []) as Row[]).map(
      (row): UserSeed => ({
        id: asString(row.id),
        userId: asString(row.user_id),
        seedId: asString(row.seed_id),
        status: (asString(row.status, "active") as SeedStatus) || "active",
        goal: asString(row.goal),
        startedAt: asString(row.started_at),
        checkIns: asCheckIns(row.check_ins),
      }),
    ),
    partnerships: ((partnerships.data ?? []) as Row[]).map(
      (row): Partnership => ({
        id: asString(row.id),
        seedId: asString(row.seed_id),
        seekerId: asString(row.seeker_id),
        partnerId: asString(row.partner_id) || null,
        goal: asString(row.goal),
        status: asString(row.status) === "matched" ? "matched" : "seeking",
        createdAt: asString(row.created_at),
        checkIns: asCheckIns(row.check_ins),
      }),
    ),
    offers: ((offers.data ?? []) as Row[]).map(
      (row): SkillOffer => ({
        id: asString(row.id),
        userId: asString(row.user_id),
        skill: asString(row.skill),
        description: asString(row.description),
        createdAt: asString(row.created_at),
      }),
    ),
    requests: ((requests.data ?? []) as Row[]).map(
      (row): SkillRequest => ({
        id: asString(row.id),
        userId: asString(row.user_id),
        skill: asString(row.skill),
        description: asString(row.description),
        createdAt: asString(row.created_at),
      }),
    ),
    connections: ((connections.data ?? []) as Row[]).map(
      (row): SkillConnection => ({
        id: asString(row.id),
        fromUserId: asString(row.from_user_id),
        toUserId: asString(row.to_user_id),
        offerId: asString(row.offer_id) || undefined,
        requestId: asString(row.request_id) || undefined,
        note: asString(row.note),
        createdAt: asString(row.created_at),
      }),
    ),
    stories: sortByNewest(
      ((stories.data ?? []) as Row[]).map(
        (row): OutTherePost => ({
          id: asString(row.id),
          authorId: asString(row.author_id),
          title: asString(row.title),
          whatDidYouDo: asString(row.what_did_you_do),
          expecting: asString(row.expecting),
          actuallyHappened: asString(row.actually_happened),
          wouldDoAgain: (asString(row.would_do_again, "yes") as WouldAgain) || "yes",
          seedId: asString(row.seed_id) || undefined,
          waypointId: asString(row.waypoint_id) || undefined,
          createdAt: asString(row.created_at),
        }),
      ),
    ),
    campfire: sortByNewest(
      ((campfire.data ?? []) as Row[]).map(
        (row): CampfirePost => ({
          id: asString(row.id),
          authorId: asString(row.author_id),
          section: asString(row.section, "general") as CampfireSection,
          kind: asString(row.kind, "thought") as CampfireKind,
          title: asString(row.title),
          body: asString(row.body),
          waypointId: asString(row.waypoint_id) || undefined,
          seedId: asString(row.seed_id) || undefined,
          outThereId: asString(row.out_there_id) || undefined,
          createdAt: asString(row.created_at),
        }),
      ),
    ),
    comments: ((comments.data ?? []) as Row[]).map(
      (row): Comment => ({
        id: asString(row.id),
        authorId: asString(row.author_id),
        targetType: asString(row.target_type) as TargetType,
        targetId: asString(row.target_id),
        body: asString(row.body),
        createdAt: asString(row.created_at),
      }),
    ),
    reactions: ((reactions.data ?? []) as Row[]).map(
      (row): Reaction => ({
        id: asString(row.id),
        userId: asString(row.user_id),
        targetType: asString(row.target_type) as TargetType,
        targetId: asString(row.target_id),
        kind: asString(row.kind) as ReactionKind,
      }),
    ),
    notifications: ((notifications.data ?? []) as Row[]).map(
      (row): AppNotification => ({
        id: asString(row.id),
        userId: asString(row.user_id),
        title: asString(row.title),
        body: asString(row.body),
        href: asString(row.href) || undefined,
        read: Boolean(row.read),
        createdAt: asString(row.created_at),
      }),
    ),
  };
}

export async function ensureProfile(supabase: SupabaseClient, user: User) {
  const { data } = await supabase.from("profiles").select("id").eq("id", user.id).maybeSingle();
  if (data) return;
  await supabase.from("profiles").insert({
    id: user.id,
    email: user.email ?? "",
    email_verified: Boolean(user.email_confirmed_at),
  });
}

const redirectTo = (next: string) => `${siteUrl()}/auth/callback?next=${encodeURIComponent(next)}`;

export async function signUp(supabase: SupabaseClient, email: string, password: string) {
  const { data, error } = await supabase.auth.signUp({
    email,
    password,
    options: { emailRedirectTo: redirectTo("/onboarding") },
  });
  return { data, error: friendlyError(error), needsVerification: !data.session };
}

export async function signIn(supabase: SupabaseClient, email: string, password: string) {
  const { error } = await supabase.auth.signInWithPassword({ email, password });
  return { error: friendlyError(error) };
}

export async function requestReset(supabase: SupabaseClient, email: string) {
  const { error } = await supabase.auth.resetPasswordForEmail(email, {
    redirectTo: redirectTo("/reset-password"),
  });
  return { error: friendlyError(error) };
}

export async function resendVerification(supabase: SupabaseClient, email: string) {
  const { error } = await supabase.auth.resend({
    type: "signup",
    email,
    options: { emailRedirectTo: redirectTo("/onboarding") },
  });
  return { error: friendlyError(error) };
}

export function profilePatch(patch: Partial<Profile>) {
  const row: Row = {};
  if (patch.displayName !== undefined) row.display_name = patch.displayName;
  if (patch.bio !== undefined) row.bio = patch.bio;
  if (patch.location !== undefined) row.location = patch.location;
  if (patch.avatarUrl !== undefined) row.avatar_url = patch.avatarUrl;
  if (patch.intentions !== undefined) row.intentions = patch.intentions;
  if (patch.onboardingComplete !== undefined) row.onboarding_complete = patch.onboardingComplete;
  if (patch.emailVerified !== undefined) row.email_verified = patch.emailVerified;
  if (patch.showLocation !== undefined) row.show_location = patch.showLocation;
  if (patch.showWaypoints !== undefined) row.show_waypoints = patch.showWaypoints;
  if (patch.notifyReplies !== undefined) row.notify_replies = patch.notifyReplies;
  if (patch.notifyWaypoints !== undefined) row.notify_waypoints = patch.notifyWaypoints;
  if (patch.notifyCheckins !== undefined) row.notify_checkins = patch.notifyCheckins;
  return row;
}
