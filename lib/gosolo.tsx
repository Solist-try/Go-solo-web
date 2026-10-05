"use client";

import {
  createContext,
  useContext,
  useEffect,
  useMemo,
  useRef,
  useState,
  type ReactNode,
} from "react";
import { defaultAdmin, mergeAdmin, note, STEWARD_EMAIL, STEWARD_ID, STEWARD_PASSWORD_HASH, type AdminState, type SiteContent } from "@/lib/admin/state";
import { applyMembers, presentWorld, resolveSeeds, resolveWaypoints } from "@/lib/admin/resolve";
import {
  createId,
  hashPassword,
  readSessionCookie,
  readStorage,
  writeSessionCookie,
  writeStorage,
} from "@/lib/session";
import { getSupabase } from "@/lib/supabase/client";
import {
  ensureProfile,
  loadWorld,
  profilePatch,
  replaceSamePreferences,
  requestReset,
  resendVerification,
  signIn,
  signUp,
} from "@/lib/supabase/data";
import { loadDesk, loadSiteContent, persistDesk } from "@/lib/supabase/admin";
import { isSupabaseConfigured } from "@/lib/supabase/env";
import {
  emptyPersisted,
  emptyWorld,
  type AppNotification,
  type CampfireKind,
  type CampfireSection,
  type PersistedState,
  type Profile,
  type ReactionKind,
  type Seed,
  type SeedStatus,
  type Waypoint,
  type World,
  type WouldAgain,
} from "@/lib/types";
import { buildView } from "@/lib/view";

type Mode = "demo" | "supabase";

type StoryInput = {
  title: string;
  whatDidYouDo: string;
  expecting: string;
  actuallyHappened: string;
  wouldDoAgain: WouldAgain;
  seedId?: string;
  waypointId?: string;
};

type CampfireInput = {
  section: CampfireSection;
  kind: CampfireKind;
  title: string;
  body: string;
  waypointId?: string;
  seedId?: string;
  outThereId?: string;
};

type StoredState = PersistedState & { admin: AdminState };

type GoSoloValue = {
  ready: boolean;
  mode: Mode;
  schemaError: string | null;
  user: Profile | null;
  world: World;
  library: World;
  seeds: Seed[];
  waypoints: Waypoint[];
  desk: AdminState;
  content: SiteContent;
  isAdmin: boolean;
  updateDesk: (updater: (admin: AdminState) => AdminState) => void;
  notifyMember: (userId: string, title: string, body: string) => void;
  register: (email: string, password: string) => Promise<{ error?: string; needsVerification?: boolean }>;
  login: (
    email: string,
    password: string,
  ) => Promise<{ error?: string; emailVerified?: boolean; onboardingComplete?: boolean }>;
  logout: () => Promise<void>;
  requestPasswordReset: (email: string) => Promise<{ error?: string; previewPath?: string }>;
  resetPassword: (password: string, token?: string) => Promise<{ error?: string }>;
  resendVerification: () => Promise<{ error?: string }>;
  confirmEmail: () => Promise<void>;
  updateProfile: (patch: Partial<Profile>, photo?: File | null) => Promise<{ error?: string }>;
  setInterests: (interests: string[]) => Promise<{ error?: string }>;
  joinWaypoint: (waypointId: string) => Promise<void>;
  leaveWaypoint: (waypointId: string) => Promise<void>;
  completeOnboarding: () => Promise<void>;
  beginSeed: (seedId: string, goal?: string) => Promise<void>;
  setSeedStatus: (userSeedId: string, status: SeedStatus) => Promise<void>;
  checkInSeed: (userSeedId: string, note: string) => Promise<void>;
  matchWith: (seedId: string, partnerId: string, goal: string) => Promise<void>;
  partnershipCheckIn: (partnershipId: string, note: string) => Promise<void>;
  offerSkill: (skill: string, description: string) => Promise<{ error?: string }>;
  requestSkill: (skill: string, description: string) => Promise<{ error?: string }>;
  connectSkill: (input: {
    toUserId: string;
    offerId?: string;
    requestId?: string;
    note: string;
  }) => Promise<void>;
  createStory: (input: StoryInput) => Promise<string>;
  reactToStory: (storyId: string, kind: ReactionKind) => Promise<void>;
  createCampfire: (input: CampfireInput) => Promise<string>;
  comment: (targetType: "out-there" | "campfire", targetId: string, body: string) => Promise<void>;
  leaveNote: (input: { name: string; email: string; body: string }) => { error?: string };
  markNotificationsRead: () => Promise<void>;
  deleteAccount: () => Promise<{ error?: string }>;
};

const GoSoloContext = createContext<GoSoloValue | null>(null);

function withSteward(state: StoredState): StoredState {
  const admin = mergeAdmin(state.admin);
  const existing = state.profiles.find((profile) => profile.email === STEWARD_EMAIL);
  if (state.accounts.some((account) => account.email === STEWARD_EMAIL)) {
    return {
      ...state,
      admin,
      profiles: state.profiles.map((profile) =>
        profile.email === STEWARD_EMAIL
          ? { ...profile, role: "admin", emailVerified: true, onboardingComplete: true, status: profile.status ?? "active" }
          : profile,
      ),
    };
  }
  return {
    ...state,
    admin,
    accounts: [
      ...state.accounts,
      { id: STEWARD_ID, email: STEWARD_EMAIL, passwordHash: STEWARD_PASSWORD_HASH, resetToken: null },
    ],
    profiles: [
      ...state.profiles,
      {
        id: existing?.id ?? STEWARD_ID,
        email: STEWARD_EMAIL,
        displayName: existing?.displayName || "Steward",
        bio: existing?.bio || "Keeps the room calm.",
        location: existing?.location ?? "",
        avatarUrl: existing?.avatarUrl ?? "",
        intentions: existing?.intentions?.length ? existing.intentions : ["curiosity"],
        interests: existing?.interests?.length ? existing.interests : ["walking"],
        onboardingComplete: true,
        emailVerified: true,
        showLocation: false,
        showWaypoints: false,
        notifyReplies: true,
        notifyWaypoints: false,
        notifyCheckins: false,
        createdAt: existing?.createdAt ?? "2026-01-01T00:00:00.000Z",
        role: "admin",
        status: "active",
      },
    ],
  };
}

function blankProfile(id: string, email: string): Profile {
  return {
    id,
    email,
    displayName: "",
    bio: "",
    location: "",
    avatarUrl: "",
    intentions: [],
    interests: [],
    onboardingComplete: false,
    emailVerified: false,
    showLocation: true,
    showWaypoints: true,
    notifyReplies: true,
    notifyWaypoints: false,
    notifyCheckins: true,
    createdAt: new Date().toISOString(),
    growing: [],
    helpGrowing: [],
    helpPlant: [],
    helpPlantNote: "",
    supportWith: [],
    checkInFrequency: "",
    checkInStyle: "",
    sameNotes: "",
  };
}

async function readPhoto(file: File) {
  if (!file.type.startsWith("image/")) throw new Error("Choose an image file.");
  if (file.size > 700_000) throw new Error("Choose an image under 700KB.");
  const dataUrl = await new Promise<string>((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(String(reader.result));
    reader.onerror = () => reject(new Error("That image did not load."));
    reader.readAsDataURL(file);
  });
  return dataUrl;
}

export function GoSoloProvider({ children }: { children: ReactNode }) {
  const [ready, setReady] = useState(false);
  const [mode, setMode] = useState<Mode>("demo");
  const [schemaError, setSchemaError] = useState<string | null>(null);
  const [persisted, setPersisted] = useState<StoredState>(() => withSteward({ ...emptyPersisted(), admin: defaultAdmin() }));
  const [remote, setRemote] = useState<World>(emptyWorld);
  const [remoteAdmin, setRemoteAdmin] = useState<AdminState>(defaultAdmin);
  const [userId, setUserId] = useState<string | null>(null);
  const persistedRef = useRef(persisted);
  const userIdRef = useRef(userId);
  const deskRef = useRef<AdminState>(defaultAdmin());
  const modeRef = useRef<Mode>("demo");

  function setSession(id: string | null) {
    userIdRef.current = id;
    setUserId(id);
  }

  function commit(updater: (state: StoredState) => StoredState) {
    const next = updater(persistedRef.current);
    persistedRef.current = next;
    setPersisted(next);
    writeStorage(next);
    deskRef.current = mergeAdmin(next.admin);
  }

  function notifyMember(userId: string, title: string, body: string) {
    if (!persistedRef.current.profiles.some((profile) => profile.id === userId)) return;
    commit((state) => ({
      ...state,
      notifications: [
        {
          id: crypto.randomUUID(),
          userId,
          title,
          body,
          href: "/dashboard",
          read: false,
          createdAt: new Date().toISOString(),
        },
        ...state.notifications,
      ],
    }));
  }

  function updateDesk(updater: (admin: AdminState) => AdminState) {
    const next = updater(mergeAdmin(deskRef.current));
    deskRef.current = next;
    if (modeRef.current === "supabase") {
      setRemoteAdmin(next);
      const supabase = getSupabase();
      if (supabase) void persistDesk(supabase, next);
      return;
    }
    commit((state) => ({ ...state, admin: next }));
  }

  async function refreshRemote(nextUserId = userIdRef.current) {
    const supabase = getSupabase();
    if (!supabase) return null;
    const {
      data: { user },
    } = await supabase.auth.getUser();
    if (user) await ensureProfile(supabase, user);
    const world = await loadWorld(supabase, user);
    setRemote(world);
    setSession(user?.id ?? nextUserId ?? null);
    return world.profiles.find((profile) => profile.id === user?.id) ?? null;
  }

  useEffect(() => {
    let cancelled = false;
    async function boot() {
      if (isSupabaseConfigured()) {
        setMode("supabase");
        try {
          const supabase = getSupabase();
          if (supabase) {
            const {
              data: { user },
            } = await supabase.auth.getUser();
            if (user) await ensureProfile(supabase, user);
            const loaded = await loadWorld(supabase, user);
            let desk = defaultAdmin();
            const content = await loadSiteContent(supabase);
            if (content) desk = { ...desk, content };
            if (user) {
              const saved = await loadDesk(supabase);
              if (saved) desk = content ? { ...saved, content: saved.content.heroTitle ? saved.content : content } : saved;
            }
            if (cancelled) return;
            deskRef.current = mergeAdmin(desk);
            setRemoteAdmin(deskRef.current);
            setRemote(loaded);
            userIdRef.current = user?.id ?? null;
            setUserId(user?.id ?? null);
          }
          modeRef.current = "supabase";
          if (!cancelled) setSchemaError(null);
        } catch (error) {
          if (!cancelled) {
            setSchemaError(
              error instanceof Error
                ? error.message
                : "Supabase is connected, but the schema is not ready.",
            );
          }
        } finally {
          if (!cancelled) setReady(true);
        }
        return;
      }

      const stored = readStorage(emptyPersisted()) as StoredState;
      const next = withSteward({ ...emptyPersisted(), ...stored, admin: mergeAdmin(stored.admin), version: 1 });
      const cookieId = readSessionCookie();
      const session = next.accounts.some((account) => account.id === cookieId) ? cookieId : null;
      if (cookieId && !session) writeSessionCookie(null);
      if (cancelled) return;
      setPersisted(next);
      persistedRef.current = next;
      deskRef.current = next.admin;
      modeRef.current = "demo";
      setSession(session);
      setMode("demo");
      setReady(true);
    }
    void boot();
    return () => {
      cancelled = true;
    };
  }, []);

  const desk = useMemo(
    () => (mode === "demo" ? mergeAdmin(persisted.admin) : remoteAdmin),
    [mode, persisted, remoteAdmin],
  );

  const library = useMemo(() => {
    const base = mode === "demo" ? buildView(persisted, true) : remote;
    const removed = new Set(desk.removedMemberIds);
    return {
      ...base,
      profiles: applyMembers(base.profiles, desk),
      stories: base.stories.filter((story) => !removed.has(story.authorId)),
      campfire: base.campfire.filter((post) => !removed.has(post.authorId)),
    };
  }, [mode, persisted, remote, desk]);

  const world = useMemo(() => presentWorld(library, desk), [library, desk]);
  const seeds = useMemo(() => resolveSeeds(desk), [desk]);
  const waypoints = useMemo(() => resolveWaypoints(desk), [desk]);

  const user = world.profiles.find((profile) => profile.id === userId) ?? null;

  async function register(email: string, password: string) {
    const normalized = email.trim().toLowerCase();
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(normalized)) {
      return { error: "Enter a valid email address." };
    }
    if (password.length < 8) return { error: "Use at least 8 characters." };

    if (mode === "supabase") {
      const supabase = getSupabase();
      if (!supabase) return { error: "Supabase is not configured." };
      const result = await signUp(supabase, normalized, password);
      if (result.error) return { error: result.error };
      if (result.data.user) await ensureProfile(supabase, result.data.user);
      if (result.data.session) {
        await refreshRemote(result.data.user?.id ?? null);
        return { needsVerification: false };
      }
      window.sessionStorage.setItem("gosolo-pending-email", normalized);
      return { needsVerification: true };
    }

    if (persistedRef.current.accounts.some((account) => account.email === normalized)) {
      return { error: "An account with that email already exists. Try logging in." };
    }
    const id = createId();
    const passwordHash = await hashPassword(password);
    commit((state) => ({
      ...state,
      accounts: [...state.accounts, { id, email: normalized, passwordHash, resetToken: null }],
      profiles: [...state.profiles, blankProfile(id, normalized)],
    }));
    setSession(id);
    writeSessionCookie(id);
    return { needsVerification: true };
  }

  async function login(email: string, password: string) {
    const normalized = email.trim().toLowerCase();
    if (mode === "supabase") {
      const supabase = getSupabase();
      if (!supabase) return { error: "Supabase is not configured." };
      const result = await signIn(supabase, normalized, password);
      if (result.error) return { error: result.error };
      const profile = await refreshRemote();
      if (profile?.status === "suspended") {
        await supabase.auth.signOut();
        setSession(null);
        return { error: "This account is paused. Write to the steward if that surprises you." };
      }
      if (profile?.status === "deactivated") {
        await supabase.auth.signOut();
        setSession(null);
        return { error: "This account is resting. Write to the steward if you want it opened again." };
      }
      return {
        emailVerified: profile?.emailVerified ?? Boolean(profile),
        onboardingComplete: profile?.onboardingComplete ?? false,
      };
    }
    const account = persistedRef.current.accounts.find((item) => item.email === normalized);
    const passwordHash = await hashPassword(password);
    if (!account || account.passwordHash !== passwordHash) {
      return { error: "That email and password do not match." };
    }
    const profile = persistedRef.current.profiles.find((item) => item.id === account.id);
    const status = mergeAdmin(persistedRef.current.admin).memberStatus[account.id] ?? profile?.status;
    if (status === "suspended") {
      return { error: "This account is paused. Write to the steward if that surprises you." };
    }
    if (status === "deactivated") {
      return { error: "This account is resting. Write to the steward if you want it opened again." };
    }
    setSession(account.id);
    writeSessionCookie(account.id);
    return {
      emailVerified: profile?.emailVerified ?? false,
      onboardingComplete: profile?.onboardingComplete ?? false,
    };
  }

  async function logout() {
    if (mode === "supabase") {
      const supabase = getSupabase();
      await supabase?.auth.signOut();
      setRemote(emptyWorld());
    }
    setSession(null);
    writeSessionCookie(null);
  }

  async function requestPasswordReset(email: string) {
    const normalized = email.trim().toLowerCase();
    if (mode === "supabase") {
      const supabase = getSupabase();
      if (!supabase) return { error: "Supabase is not configured." };
      const result = await requestReset(supabase, normalized);
      return { error: result.error ?? undefined };
    }
    const account = persistedRef.current.accounts.find((item) => item.email === normalized);
    if (!account) return {};
    const token = createId();
    commit((state) => ({
      ...state,
      accounts: state.accounts.map((item) =>
        item.id === account.id ? { ...item, resetToken: token } : item,
      ),
    }));
    return { previewPath: `/reset-password?token=${token}` };
  }

  async function resetPassword(password: string, token?: string) {
    if (password.length < 8) return { error: "Use at least 8 characters." };
    if (mode === "supabase") {
      const supabase = getSupabase();
      if (!supabase) return { error: "Supabase is not configured." };
      const { error } = await supabase.auth.updateUser({ password });
      return { error: error ? "The reset link is no longer active. Request a new one." : undefined };
    }
    const signedIn = userIdRef.current;
    if (signedIn && !token) {
      const passwordHash = await hashPassword(password);
      commit((state) => ({
        ...state,
        accounts: state.accounts.map((item) => (item.id === signedIn ? { ...item, passwordHash } : item)),
      }));
      return {};
    }
    const account = persistedRef.current.accounts.find((item) => item.resetToken && item.resetToken === token);
    if (!account) return { error: "This reset link is no longer active." };
    const passwordHash = await hashPassword(password);
    commit((state) => ({
      ...state,
      accounts: state.accounts.map((item) =>
        item.id === account.id ? { ...item, passwordHash, resetToken: null } : item,
      ),
    }));
    return {};
  }

  async function resend() {
    if (mode === "supabase") {
      const supabase = getSupabase();
      const email =
        user?.email ||
        (typeof window !== "undefined" ? window.sessionStorage.getItem("gosolo-pending-email") : "") ||
        "";
      if (!supabase || !email) return { error: "Enter the email you registered with." };
      return resendVerification(supabase, email);
    }
    return {};
  }

  async function confirmEmail() {
    const id = userIdRef.current;
    if (!id || mode !== "demo") return;
    commit((state) => ({
      ...state,
      profiles: state.profiles.map((profile) =>
        profile.id === id ? { ...profile, emailVerified: true } : profile,
      ),
    }));
  }

  async function updateProfile(patch: Partial<Profile>, photo?: File | null) {
    const id = userIdRef.current;
    if (!id) return { error: "Sign in first." };
    let avatarUrl = patch.avatarUrl;
    try {
      if (photo) {
        if (mode === "supabase") {
          const supabase = getSupabase();
          if (!supabase) return { error: "Supabase is not configured." };
          const extension = photo.type.includes("png") ? "png" : "jpg";
          const path = `${id}/avatar.${extension}`;
          const { error } = await supabase.storage.from("avatars").upload(path, photo, {
            upsert: true,
            contentType: photo.type,
          });
          if (error) return { error: "Photo storage is not ready yet. The rest can still be saved." };
          const { data } = supabase.storage.from("avatars").getPublicUrl(path);
          avatarUrl = `${data.publicUrl}?v=${Date.now()}`;
        } else {
          avatarUrl = await readPhoto(photo);
        }
      }
    } catch (error) {
      return { error: error instanceof Error ? error.message : "That photo did not save." };
    }

    const nextPatch = { ...patch, ...(avatarUrl !== undefined ? { avatarUrl } : {}) };
    if (mode === "supabase") {
      const supabase = getSupabase();
      if (!supabase) return { error: "Supabase is not configured." };
      const { error } = await supabase.from("profiles").update(profilePatch(nextPatch)).eq("id", id);
      if (error) return { error: "Your profile could not be saved." };
      const preferenceError = await replaceSamePreferences(supabase, id, nextPatch);
      if (preferenceError) return { error: preferenceError };
      await refreshRemote();
      return {};
    }

    commit((state) => ({
      ...state,
      profiles: state.profiles.map((profile) => (profile.id === id ? { ...profile, ...nextPatch } : profile)),
    }));
    return {};
  }

  async function setInterests(interests: string[]) {
    const id = userIdRef.current;
    if (!id) return { error: "Sign in first." };
    if (mode === "supabase") {
      const supabase = getSupabase();
      if (!supabase) return { error: "Supabase is not configured." };
      const { error: deleteError } = await supabase.from("user_interests").delete().eq("user_id", id);
      if (deleteError) return { error: "Your interests could not be saved." };
      if (interests.length > 0) {
        const { error } = await supabase.from("user_interests").insert(
          interests.map((interest) => ({ user_id: id, interest_slug: interest })),
        );
        if (error) return { error: "Your interests could not be saved." };
      }
      await refreshRemote();
      return {};
    }
    commit((state) => ({
      ...state,
      profiles: state.profiles.map((profile) => (profile.id === id ? { ...profile, interests } : profile)),
    }));
    return {};
  }

  async function joinWaypoint(waypointId: string) {
    const id = userIdRef.current;
    if (!id) return;
    if (world.memberships.some((item) => item.userId === id && item.waypointId === waypointId)) return;
    if (mode === "supabase") {
      const supabase = getSupabase();
      if (!supabase) return;
      await supabase.from("user_waypoints").insert({ user_id: id, waypoint_id: waypointId });
      await refreshRemote();
      return;
    }
    commit((state) => ({
      ...state,
      memberships: [...state.memberships, { userId: id, waypointId, joinedAt: new Date().toISOString() }],
    }));
  }

  async function leaveWaypoint(waypointId: string) {
    const id = userIdRef.current;
    if (!id) return;
    if (mode === "supabase") {
      const supabase = getSupabase();
      if (!supabase) return;
      await supabase.from("user_waypoints").delete().eq("user_id", id).eq("waypoint_id", waypointId);
      await refreshRemote();
      return;
    }
    commit((state) => ({
      ...state,
      memberships: state.memberships.filter(
        (item) => !(item.userId === id && item.waypointId === waypointId),
      ),
    }));
  }

  async function completeOnboarding() {
    await updateProfile({ onboardingComplete: true });
  }

  async function beginSeed(seedId: string, goal = "") {
    const id = userIdRef.current;
    if (!id) return;
    const existing = world.userSeeds.find(
      (item) => item.userId === id && item.seedId === seedId && item.status === "active",
    );
    if (existing) return;
    const seed = resolveSeeds(mergeAdmin(deskRef.current)).find((item) => item.id === seedId);
    if (!seed) return;
    if (mode === "supabase") {
      const supabase = getSupabase();
      if (!supabase) return;
      const { data, error } = await supabase
        .from("user_seeds")
        .insert({ user_id: id, seed_id: seedId, goal, status: "active" })
        .select("id")
        .single();
      if (error || !data) return;
      if (seed?.kind === "same") {
        await supabase.from("same_partnerships").insert({
          user_seed_id: data.id,
          seeker_id: id,
          seed_id: seedId,
          goal: goal || seed.prompt,
          status: "seeking",
        });
      }
      await refreshRemote();
      return;
    }
    const userSeedId = createId();
    commit((state) => ({
      ...state,
      userSeeds: [
        ...state.userSeeds,
        {
          id: userSeedId,
          userId: id,
          seedId,
          status: "active",
          goal,
          startedAt: new Date().toISOString(),
          checkIns: [],
        },
      ],
      partnerships:
        seed?.kind === "same"
          ? [
              ...state.partnerships,
              {
                id: createId(),
                seedId,
                seekerId: id,
                partnerId: null,
                goal: goal || seed.prompt,
                status: "seeking",
                createdAt: new Date().toISOString(),
                checkIns: [],
              },
            ]
          : state.partnerships,
    }));
  }

  async function setSeedStatus(userSeedId: string, status: SeedStatus) {
    const id = userIdRef.current;
    if (!id) return;
    if (mode === "supabase") {
      const supabase = getSupabase();
      if (!supabase) return;
      await supabase.from("user_seeds").update({ status }).eq("id", userSeedId).eq("user_id", id);
      await refreshRemote();
      return;
    }
    commit((state) => ({
      ...state,
      userSeeds: state.userSeeds.map((item) => (item.id === userSeedId ? { ...item, status } : item)),
    }));
  }

  async function checkInSeed(userSeedId: string, note: string) {
    const text = note.trim();
    if (!text) return;
    const current = (mode === "demo" ? buildView(persistedRef.current, true) : remote).userSeeds.find(
      (item) => item.id === userSeedId,
    );
    if (!current) return;
    const checkIns = [...current.checkIns, { id: createId(), at: new Date().toISOString(), note: text }];
    if (mode === "supabase") {
      const supabase = getSupabase();
      if (!supabase) return;
      await supabase.from("user_seeds").update({ check_ins: checkIns }).eq("id", userSeedId);
      await refreshRemote();
      return;
    }
    commit((state) => ({
      ...state,
      userSeeds: state.userSeeds.map((item) => (item.id === userSeedId ? { ...item, checkIns } : item)),
    }));
  }

  async function matchWith(seedId: string, partnerId: string, goal: string) {
    const id = userIdRef.current;
    if (!id) return;
    const text = goal.trim();
    if (!text) return;
    if (mode === "supabase") {
      const supabase = getSupabase();
      if (!supabase) return;
      const { data } = await supabase
        .from("user_seeds")
        .insert({ user_id: id, seed_id: seedId, goal: text, status: "active" })
        .select("id")
        .single();
      await supabase.from("same_partnerships").insert({
        user_seed_id: data?.id,
        seeker_id: id,
        partner_id: partnerId,
        seed_id: seedId,
        goal: text,
        status: "matched",
      });
      await supabase.rpc("notify", {
        target: partnerId,
        title: "Someone would like to walk this stretch with you",
        body: "A weekly check-in is waiting. Missing a week is allowed.",
        href: `/seeds/${seedId}`,
      });
      await refreshRemote();
      return;
    }
    const notice: AppNotification = {
      id: createId(),
      userId: id,
      title: "You have a companion for this stretch",
      body: "Check in when the week has something to say. Missing a week is allowed.",
      href: `/seeds/${seedId}`,
      read: false,
      createdAt: new Date().toISOString(),
    };
    commit((state) => ({
      ...state,
      userSeeds: state.userSeeds.some((item) => item.userId === id && item.seedId === seedId && item.status === "active")
        ? state.userSeeds
        : [
            ...state.userSeeds,
            {
              id: createId(),
              userId: id,
              seedId,
              status: "active",
              goal: text,
              startedAt: new Date().toISOString(),
              checkIns: [],
            },
          ],
      partnerships: [
        ...state.partnerships.filter((item) => !(item.seekerId === id && item.seedId === seedId && item.status === "seeking")),
        {
          id: createId(),
          seedId,
          seekerId: id,
          partnerId,
          goal: text,
          status: "matched",
          createdAt: new Date().toISOString(),
          checkIns: [],
        },
      ],
      notifications: [notice, ...state.notifications],
    }));
  }

  async function partnershipCheckIn(partnershipId: string, note: string) {
    const id = userIdRef.current;
    const text = note.trim();
    if (!id || !text) return;
    const current = (mode === "demo" ? buildView(persistedRef.current, true) : remote).partnerships.find(
      (item) => item.id === partnershipId,
    );
    if (!current) return;
    const checkIns = [
      ...current.checkIns,
      { id: createId(), at: new Date().toISOString(), note: text, userId: id },
    ];
    if (mode === "supabase") {
      const supabase = getSupabase();
      if (!supabase) return;
      await supabase.from("same_partnerships").update({ check_ins: checkIns }).eq("id", partnershipId);
      await refreshRemote();
      return;
    }
    const persistedMatch = persistedRef.current.partnerships.some((item) => item.id === partnershipId);
    if (!persistedMatch) return;
    commit((state) => ({
      ...state,
      partnerships: state.partnerships.map((item) => (item.id === partnershipId ? { ...item, checkIns } : item)),
    }));
  }

  async function offerSkill(skill: string, description: string) {
    const id = userIdRef.current;
    if (!id) return { error: "Sign in first." };
    if (!skill.trim() || !description.trim()) return { error: "Say what you can share, and a little about how." };
    if (mode === "supabase") {
      const supabase = getSupabase();
      if (!supabase) return { error: "Supabase is not configured." };
      await supabase.from("skill_offers").insert({
        user_id: id,
        skill: skill.trim(),
        description: description.trim(),
      });
      await refreshRemote();
      return {};
    }
    commit((state) => ({
      ...state,
      offers: [
        ...state.offers,
        {
          id: createId(),
          userId: id,
          skill: skill.trim(),
          description: description.trim(),
          createdAt: new Date().toISOString(),
        },
      ],
    }));
    return {};
  }

  async function requestSkill(skill: string, description: string) {
    const id = userIdRef.current;
    if (!id) return { error: "Sign in first." };
    if (!skill.trim() || !description.trim()) return { error: "Say what you would like to learn." };
    if (mode === "supabase") {
      const supabase = getSupabase();
      if (!supabase) return { error: "Supabase is not configured." };
      await supabase.from("skill_requests").insert({
        user_id: id,
        skill: skill.trim(),
        description: description.trim(),
      });
      await refreshRemote();
      return {};
    }
    commit((state) => ({
      ...state,
      requests: [
        ...state.requests,
        {
          id: createId(),
          userId: id,
          skill: skill.trim(),
          description: description.trim(),
          createdAt: new Date().toISOString(),
        },
      ],
    }));
    return {};
  }

  async function connectSkill(input: { toUserId: string; offerId?: string; requestId?: string; note: string }) {
    const id = userIdRef.current;
    if (!id) return;
    if (mode === "supabase") {
      const supabase = getSupabase();
      if (!supabase) return;
      await supabase.from("skill_connections").insert({
        from_user_id: id,
        to_user_id: input.toUserId,
        offer_id: input.offerId ?? null,
        request_id: input.requestId ?? null,
        note: input.note.trim(),
      });
      await supabase.rpc("notify", {
        target: input.toUserId,
        title: "Someone would like to trade a skill",
        body: input.note.trim() || "A small exchange is waiting.",
        href: "/seeds/skill-swap",
      });
      await refreshRemote();
      return;
    }
    commit((state) => ({
      ...state,
      connections: [
        ...state.connections,
        {
          id: createId(),
          fromUserId: id,
          toUserId: input.toUserId,
          offerId: input.offerId,
          requestId: input.requestId,
          note: input.note.trim(),
          createdAt: new Date().toISOString(),
        },
      ],
    }));
  }

  async function createStory(input: StoryInput) {
    const id = userIdRef.current;
    if (!id) return "";
    if (mode === "supabase") {
      const supabase = getSupabase();
      if (!supabase) return "";
      const { data } = await supabase
        .from("out_there_posts")
        .insert({
          author_id: id,
          title: input.title.trim(),
          what_did_you_do: input.whatDidYouDo.trim(),
          expecting: input.expecting.trim(),
          actually_happened: input.actuallyHappened.trim(),
          would_do_again: input.wouldDoAgain,
          seed_id: input.seedId || null,
          waypoint_id: input.waypointId || null,
        })
        .select("id")
        .single();
      await refreshRemote();
      return data?.id ?? "";
    }
    const storyId = createId();
    commit((state) => ({
      ...state,
      stories: [
        {
          id: storyId,
          authorId: id,
          title: input.title.trim(),
          whatDidYouDo: input.whatDidYouDo.trim(),
          expecting: input.expecting.trim(),
          actuallyHappened: input.actuallyHappened.trim(),
          wouldDoAgain: input.wouldDoAgain,
          seedId: input.seedId,
          waypointId: input.waypointId,
          createdAt: new Date().toISOString(),
        },
        ...state.stories,
      ],
    }));
    return storyId;
  }

  async function reactToStory(storyId: string, kind: ReactionKind) {
    const id = userIdRef.current;
    if (!id) return;
    const existing = world.reactions.find(
      (reaction) => reaction.userId === id && reaction.targetId === storyId && reaction.targetType === "out-there",
    );
    if (mode === "supabase") {
      const supabase = getSupabase();
      if (!supabase) return;
      if (existing?.kind === kind) {
        await supabase.from("reactions").delete().eq("id", existing.id);
      } else if (existing) {
        await supabase.from("reactions").update({ kind }).eq("id", existing.id);
      } else {
        await supabase.from("reactions").insert({
          user_id: id,
          target_type: "out-there",
          target_id: storyId,
          kind,
        });
      }
      await refreshRemote();
      return;
    }
    commit((state) => {
      const mine = state.reactions.find(
        (reaction) => reaction.userId === id && reaction.targetId === storyId,
      );
      if (mine?.kind === kind) {
        return { ...state, reactions: state.reactions.filter((reaction) => reaction.id !== mine.id) };
      }
      if (mine) {
        return {
          ...state,
          reactions: state.reactions.map((reaction) => (reaction.id === mine.id ? { ...reaction, kind } : reaction)),
        };
      }
      return {
        ...state,
        reactions: [
          ...state.reactions,
          { id: createId(), userId: id, targetType: "out-there", targetId: storyId, kind },
        ],
      };
    });
  }

  async function createCampfire(input: CampfireInput) {
    const id = userIdRef.current;
    if (!id) return "";
    if (mode === "supabase") {
      const supabase = getSupabase();
      if (!supabase) return "";
      const { data } = await supabase
        .from("campfire_posts")
        .insert({
          author_id: id,
          section: input.section,
          kind: input.kind,
          title: input.title.trim(),
          body: input.body.trim(),
          waypoint_id: input.waypointId || null,
          seed_id: input.seedId || null,
          out_there_id: input.outThereId || null,
        })
        .select("id")
        .single();
      await refreshRemote();
      return data?.id ?? "";
    }
    const postId = createId();
    commit((state) => ({
      ...state,
      campfire: [
        {
          id: postId,
          authorId: id,
          section: input.section,
          kind: input.kind,
          title: input.title.trim(),
          body: input.body.trim(),
          waypointId: input.waypointId,
          seedId: input.seedId,
          outThereId: input.outThereId,
          createdAt: new Date().toISOString(),
        },
        ...state.campfire,
      ],
    }));
    return postId;
  }

  async function comment(targetType: "out-there" | "campfire", targetId: string, body: string) {
    const id = userIdRef.current;
    const text = body.trim();
    if (!id || !text) return;
    if (targetType === "campfire" && mergeAdmin(deskRef.current).campfireModeration[targetId]?.locked) return;
    const source =
      targetType === "campfire"
        ? world.campfire.find((post) => post.id === targetId)
        : world.stories.find((story) => story.id === targetId);
    const authorId = source && "authorId" in source ? source.authorId : undefined;
    const author = world.profiles.find((profile) => profile.id === authorId);
    if (mode === "supabase") {
      const supabase = getSupabase();
      if (!supabase) return;
      await supabase.from("comments").insert({
        author_id: id,
        target_type: targetType,
        target_id: targetId,
        body: text,
      });
      if (authorId && authorId !== id && author?.notifyReplies) {
        await supabase.rpc("notify", {
          target: authorId,
          title: "Someone sat with what you shared",
          body: text.slice(0, 140),
          href: targetType === "campfire" ? `/campfire/${targetId}` : `/out-there/${targetId}`,
        });
      }
      await refreshRemote();
      return;
    }
    const notification: AppNotification | null =
      authorId && authorId !== id && author?.notifyReplies
        ? {
            id: createId(),
            userId: authorId,
            title: "Someone sat with what you shared",
            body: text.slice(0, 140),
            href: targetType === "campfire" ? `/campfire/${targetId}` : `/out-there/${targetId}`,
            read: false,
            createdAt: new Date().toISOString(),
          }
        : null;
    commit((state) => ({
      ...state,
      comments: [
        ...state.comments,
        {
          id: createId(),
          authorId: id,
          targetType,
          targetId,
          body: text,
          createdAt: new Date().toISOString(),
        },
      ],
      notifications: notification ? [notification, ...state.notifications] : state.notifications,
    }));
  }

  async function markNotificationsRead() {
    const id = userIdRef.current;
    if (!id) return;
    if (mode === "supabase") {
      const supabase = getSupabase();
      if (!supabase) return;
      await supabase.from("notifications").update({ read: true }).eq("user_id", id).eq("read", false);
      await refreshRemote();
      return;
    }
    commit((state) => ({
      ...state,
      notifications: state.notifications.map((item) =>
        item.userId === id ? { ...item, read: true } : item,
      ),
    }));
  }

  async function deleteAccount() {
    const id = userIdRef.current;
    if (!id) return { error: "Sign in first." };
    if (mode === "supabase") {
      const supabase = getSupabase();
      if (!supabase) return { error: "Supabase is not configured." };
      const { error } = await supabase.rpc("delete_own_account");
      if (error) {
        return { error: "Account deletion needs the database function from the migration." };
      }
      await supabase.auth.signOut();
      setRemote(emptyWorld());
      setSession(null);
      return {};
    }
    commit((state) => ({
      ...state,
      accounts: state.accounts.filter((account) => account.id !== id),
      profiles: state.profiles.filter((profile) => profile.id !== id),
      userSeeds: state.userSeeds.filter((item) => item.userId !== id),
      memberships: state.memberships.filter((item) => item.userId !== id),
      partnerships: state.partnerships.filter((item) => item.seekerId !== id && item.partnerId !== id),
      offers: state.offers.filter((item) => item.userId !== id),
      requests: state.requests.filter((item) => item.userId !== id),
      connections: state.connections.filter((item) => item.fromUserId !== id && item.toUserId !== id),
      stories: state.stories.filter((item) => item.authorId !== id),
      campfire: state.campfire.filter((item) => item.authorId !== id),
      comments: state.comments.filter((item) => item.authorId !== id),
      reactions: state.reactions.filter((item) => item.userId !== id),
      notifications: state.notifications.filter((item) => item.userId !== id),
    }));
    setSession(null);
    writeSessionCookie(null);
    return {};
  }

  function leaveNote(input: { name: string; email: string; body: string }) {
    const name = input.name.trim();
    const email = input.email.trim();
    const body = input.body.trim();
    if (!name || !email || !body) return { error: "A name, a way back to you, and a few sentences are enough." };
    updateDesk((admin) =>
      note(
        {
          ...admin,
          letters: [
            { id: crypto.randomUUID(), name, email, body, createdAt: new Date().toISOString() },
            ...admin.letters,
          ].slice(0, 80),
        },
        "A note arrived for the founder.",
      ),
    );
    return {};
  }

  const value: GoSoloValue = {
    ready,
    mode,
    schemaError,
    user,
    world,
    library,
    seeds,
    waypoints,
    desk,
    content: desk.content,
    isAdmin: user?.role === "admin",
    updateDesk,
    notifyMember,
    register,
    login,
    logout,
    requestPasswordReset,
    resetPassword,
    resendVerification: resend,
    confirmEmail,
    updateProfile,
    setInterests,
    joinWaypoint,
    leaveWaypoint,
    completeOnboarding,
    beginSeed,
    setSeedStatus,
    checkInSeed,
    matchWith,
    partnershipCheckIn,
    offerSkill,
    requestSkill,
    connectSkill,
    createStory,
    reactToStory,
    createCampfire,
    comment,
    leaveNote,
    markNotificationsRead,
    deleteAccount,
  };

  return <GoSoloContext.Provider value={value}>{children}</GoSoloContext.Provider>;
}

export function useGoSolo() {
  const value = useContext(GoSoloContext);
  if (!value) throw new Error("useGoSolo must be used within GoSoloProvider");
  return value;
}

export function useCatalog() {
  const { seeds, waypoints } = useGoSolo();
  return {
    getSeed(id?: string) {
      if (!id) return undefined;
      return seeds.find((seed) => seed.id === id);
    },
    getWaypoint(idOrSlug?: string) {
      if (!idOrSlug) return undefined;
      return waypoints.find((waypoint) => waypoint.id === idOrSlug || waypoint.slug === idOrSlug);
    },
  };
}
