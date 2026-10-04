import { INTENTIONS, INTERESTS } from "@/lib/types";
import type { CampfirePost, OutTherePost, Profile, Seed, UserSeed, Waypoint, World } from "@/lib/types";
import { categoryLabel, kindLabel } from "@/lib/format";

const DAY = 86_400_000;

export type ShareRow = {
  id: string;
  label: string;
  count: number;
  percent: number;
};

export type HealthPart = {
  label: string;
  score: number;
  of: number;
  detail: string;
};

export type Insights = {
  members: number;
  newMembers: number;
  weeklyActive: number;
  newStories: number;
  newCampfire: number;
  seedsStarted: number;
  seedsStartedThisWeek: number;
  seedsCompleted: number;
  sameMatches: number;
  skillSwaps: number;
  waypoints: { waypoint: Waypoint; members: number; recent: number }[];
  usedSeeds: { seed: Seed; starts: number }[];
  topics: ShareRow[];
  joined: ShareRow[];
  interests: ShareRow[];
  joinedWaypoints: { waypoint: Waypoint; members: number }[];
  startedSeeds: { seed: Seed; starts: number }[];
  completedSeeds: { seed: Seed; completions: number }[];
  storyCategories: ShareRow[];
  retention: { days: number; eligible: number; returned: number; percent: number }[];
  health: { score: number; reading: string; parts: HealthPart[] };
  popularStories: { story: OutTherePost; responses: number }[];
  themes: ShareRow[];
  locations: { location: string; when: string }[];
  activities: ShareRow[];
  openQuestions: CampfirePost[];
  unanswered: CampfirePost[];
  activeConversations: { post: CampfirePost; replies: number }[];
};

function time(iso?: string) {
  if (!iso) return 0;
  const value = new Date(iso).getTime();
  return Number.isNaN(value) ? 0 : value;
}

function since(iso: string | undefined, days: number, now: number) {
  const value = time(iso);
  return value > 0 && now - value <= days * DAY && value <= now;
}

function activityTimes(userId: string, world: World) {
  const times: number[] = [];
  for (const story of world.stories) if (story.authorId === userId) times.push(time(story.createdAt));
  for (const post of world.campfire) if (post.authorId === userId) times.push(time(post.createdAt));
  for (const comment of world.comments) if (comment.authorId === userId) times.push(time(comment.createdAt));
  for (const seed of world.userSeeds) {
    if (seed.userId !== userId) continue;
    times.push(time(seed.startedAt));
    for (const check of seed.checkIns) times.push(time(check.at));
  }
  for (const item of world.partnerships) {
    if (item.seekerId === userId || item.partnerId === userId) {
      times.push(time(item.createdAt));
      for (const check of item.checkIns) if (!check.userId || check.userId === userId) times.push(time(check.at));
    }
  }
  for (const item of world.connections) {
    if (item.fromUserId === userId || item.toUserId === userId) times.push(time(item.createdAt));
  }
  return times.filter((value) => value > 0);
}

export function lastActiveAt(profile: Profile, world: World) {
  const times = activityTimes(profile.id, world);
  const latest = Math.max(time(profile.createdAt), ...times, 0);
  return new Date(latest).toISOString();
}

function share(rows: { id: string; label: string; count: number }[], base: number): ShareRow[] {
  return rows
    .filter((row) => row.count > 0)
    .sort((a, b) => b.count - a.count)
    .map((row) => ({
      ...row,
      percent: base > 0 ? Math.round((row.count / base) * 100) : 0,
    }));
}

function retention(days: number, members: Profile[], world: World, now: number) {
  const eligible = members.filter((member) => now - time(member.createdAt) >= days * DAY);
  const returned = eligible.filter((member) =>
    activityTimes(member.id, world).some((value) => value >= time(member.createdAt) + days * DAY),
  );
  return {
    days,
    eligible: eligible.length,
    returned: returned.length,
    percent: eligible.length ? Math.round((returned.length / eligible.length) * 100) : 0,
  };
}

const THEMES: [string, string[]][] = [
  ["Museums", ["museum", "gallery", "exhibit"]],
  ["Walking", ["walk", "street", "path"]],
  ["Meals", ["dinner", "cook", "meal", "eat", "table"]],
  ["Home", ["home", "room", "apartment", "kitchen"]],
  ["Travel", ["trip", "train", "ticket", "city"]],
  ["Making", ["paint", "photo", "pottery", "write", "camera"]],
];

function storyText(story: OutTherePost) {
  return `${story.title} ${story.whatDidYouDo} ${story.actuallyHappened}`.toLowerCase();
}

function reading(score: number) {
  if (score >= 80) return "The room is well. People are returning, and seeds are becoming stories.";
  if (score >= 60) return "The room is steady. There is enough action to learn from, and enough quiet to trust.";
  if (score >= 40) return "The room is quiet. People are here, and a few more finished seeds would tell you more.";
  return "The room is just beginning. Watch who returns, and which seeds actually leave the house.";
}

export function buildInsights(input: {
  world: World;
  seeds: Seed[];
  waypoints: Waypoint[];
  now?: Date;
}): Insights {
  const now = (input.now ?? new Date()).getTime();
  const { world, seeds, waypoints } = input;
  const members = world.profiles.filter((profile) => profile.role !== "admin");
  const memberIds = new Set(members.map((member) => member.id));
  const stories = world.stories.filter((story) => memberIds.has(story.authorId));
  const campfire = world.campfire.filter((post) => memberIds.has(post.authorId));
  const seedsById = new Map(seeds.map((seed) => [seed.id, seed]));
  const pointsById = new Map(waypoints.map((waypoint) => [waypoint.id, waypoint]));

  const weeklyActive = members.filter((member) =>
    activityTimes(member.id, world).some((value) => now - value <= 7 * DAY && value <= now),
  ).length;

  const userSeeds = world.userSeeds.filter((seed) => memberIds.has(seed.userId));
  const starts = new Map<string, number>();
  const completions = new Map<string, number>();
  for (const item of userSeeds) {
    starts.set(item.seedId, (starts.get(item.seedId) ?? 0) + 1);
    if (item.status === "completed") completions.set(item.seedId, (completions.get(item.seedId) ?? 0) + 1);
  }

  const waypointMembers = new Map<string, number>();
  for (const item of world.memberships) {
    if (!memberIds.has(item.userId)) continue;
    waypointMembers.set(item.waypointId, (waypointMembers.get(item.waypointId) ?? 0) + 1);
  }

  const recentByPoint = new Map<string, number>();
  for (const story of stories) {
    if (story.waypointId && since(story.createdAt, 30, now)) {
      recentByPoint.set(story.waypointId, (recentByPoint.get(story.waypointId) ?? 0) + 1);
    }
  }
  for (const post of campfire) {
    if (post.waypointId && since(post.createdAt, 30, now)) {
      recentByPoint.set(post.waypointId, (recentByPoint.get(post.waypointId) ?? 0) + 1);
    }
  }

  const intentionCounts = INTENTIONS.map((item) => ({
    id: item.id,
    label: item.label,
    count: members.filter((member) => member.intentions.includes(item.id)).length,
  }));
  const named = intentionCounts.reduce((sum, item) => sum + item.count, 0);
  const other = members.filter((member) => member.intentions.length === 0).length;

  const interestCounts = INTERESTS.map((item) => ({
    id: item.id,
    label: item.label,
    count: members.filter((member) => member.interests.includes(item.id) || (item.id === "diy" && member.interests.includes("home-diy"))).length,
  }));

  const categoryCounts = new Map<string, number>();
  for (const story of stories) {
    const category = story.seedId ? seedsById.get(story.seedId)?.category ?? "unsorted" : "unsorted";
    categoryCounts.set(category, (categoryCounts.get(category) ?? 0) + 1);
  }

  const themeRows = THEMES.map(([label, words]) => ({
    id: label.toLowerCase(),
    label,
    count: stories.filter((story) => words.some((word) => storyText(story).includes(word))).length,
  }));

  const activityRows = [...starts.entries()]
    .map(([id, count]) => ({
      id,
      label: seedsById.get(id)?.title ?? "A seed",
      count: stories.filter((story) => story.seedId === id).length || count,
    }))
    .filter((row) => stories.some((story) => story.seedId === row.id));

  const replyCount = (postId: string) =>
    world.comments.filter((comment) => comment.targetType === "campfire" && comment.targetId === postId).length;

  const questions = campfire.filter((post) => post.kind === "question");
  const seven = retention(7, members, world, now);
  const thirty = retention(30, members, world, now);
  const ninety = retention(90, members, world, now);

  const monthStories = stories.filter((story) => since(story.createdAt, 30, now)).length;
  const monthFire = campfire.filter((post) => since(post.createdAt, 30, now)).length;
  const completionRate = userSeeds.length
    ? userSeeds.filter((seed) => seed.status === "completed").length / userSeeds.length
    : 0.5;
  const samePeople = new Set(
    world.partnerships
      .filter((item) => item.status === "matched")
      .flatMap((item) => [item.seekerId, item.partnerId].filter((id): id is string => Boolean(id))),
  );
  const returnScore = Math.round((seven.percent / 100) * 25);
  const storyScore = Math.round(Math.min(20, members.length ? (monthStories / members.length) * 20 : 0));
  const fireScore = Math.round(Math.min(20, members.length ? (monthFire / members.length) * 20 : 0));
  const seedScore = Math.round(completionRate * 20);
  const sameScore = Math.round(Math.min(15, members.length ? (samePeople.size / members.length) * 15 : 0));
  const healthScore = Math.min(100, returnScore + storyScore + fireScore + seedScore + sameScore);

  const rankedSeeds = (map: Map<string, number>) =>
    [...map.entries()]
      .map(([id, count]) => ({ seed: seedsById.get(id), count }))
      .filter((item): item is { seed: Seed; count: number } => Boolean(item.seed))
      .sort((a, b) => b.count - a.count);

  const used = rankedSeeds(starts);

  return {
    members: members.length,
    newMembers: members.filter((member) => since(member.createdAt, 7, now)).length,
    weeklyActive,
    newStories: stories.filter((story) => since(story.createdAt, 7, now)).length,
    newCampfire: campfire.filter((post) => since(post.createdAt, 7, now)).length,
    seedsStarted: userSeeds.length,
    seedsStartedThisWeek: userSeeds.filter((seed) => since(seed.startedAt, 7, now)).length,
    seedsCompleted: userSeeds.filter((seed) => seed.status === "completed").length,
    sameMatches: world.partnerships.filter((item) => item.status === "matched" && memberIds.has(item.seekerId)).length,
    skillSwaps: world.connections.filter((item) => memberIds.has(item.fromUserId)).length,
    waypoints: waypoints
      .map((waypoint) => ({
        waypoint,
        members: waypointMembers.get(waypoint.id) ?? 0,
        recent: recentByPoint.get(waypoint.id) ?? 0,
      }))
      .sort((a, b) => b.recent + b.members - (a.recent + a.members)),
    usedSeeds: used.slice(0, 5).map((item) => ({ seed: item.seed, starts: item.count })),
    topics: share(
      [...new Set(campfire.map((post) => post.kind))].map((kind) => ({
        id: kind,
        label: kindLabel(kind),
        count: campfire.filter((post) => post.kind === kind).length,
      })),
      campfire.length,
    ),
    joined: share(
      other > 0 ? [...intentionCounts, { id: "other", label: "Other", count: other }] : intentionCounts,
      named + other || members.length,
    ),
    interests: share(interestCounts, members.length).slice(0, 5),
    joinedWaypoints: [...waypointMembers.entries()]
      .map(([id, count]) => ({ waypoint: pointsById.get(id), members: count }))
      .filter((item): item is { waypoint: Waypoint; members: number } => Boolean(item.waypoint))
      .sort((a, b) => b.members - a.members),
    startedSeeds: used.map((item) => ({ seed: item.seed, starts: item.count })),
    completedSeeds: rankedSeeds(completions).map((item) => ({ seed: item.seed, completions: item.count })),
    storyCategories: share(
      [...categoryCounts.entries()].map(([id, count]) => ({
        id,
        label: id === "unsorted" ? "Not tied to a seed" : categoryLabel(id),
        count,
      })),
      stories.length,
    ),
    retention: [seven, thirty, ninety],
    health: {
      score: healthScore,
      reading: reading(healthScore),
      parts: [
        {
          label: "Return visits",
          score: returnScore,
          of: 25,
          detail:
            seven.eligible === 0
              ? "No one has been here long enough for a 7-day return yet."
              : `${seven.returned} of ${seven.eligible} people who joined at least 7 days ago came back after that first week.`,
        },
        {
          label: "Out There activity",
          score: storyScore,
          of: 20,
          detail: `${monthStories} ${monthStories === 1 ? "story" : "stories"} from the last 30 days. This reads real-world action, not time spent here.`,
        },
        {
          label: "Campfire activity",
          score: fireScore,
          of: 20,
          detail: `${monthFire} campfire ${monthFire === 1 ? "post" : "posts"} in the last 30 days. Conversation is a return, not a performance.`,
        },
        {
          label: "Seed completion",
          score: seedScore,
          of: 20,
          detail: userSeeds.length
            ? `${userSeeds.filter((seed) => seed.status === "completed").length} of ${userSeeds.length} started seeds have been completed.`
            : "No seeds have been started yet, so this part stays neutral.",
        },
        {
          label: "SAME participation",
          score: sameScore,
          of: 15,
          detail: `${samePeople.size} ${samePeople.size === 1 ? "person is" : "people are"} in a matched SAME partnership.`,
        },
      ],
    },
    popularStories: stories
      .map((story) => ({
        story,
        responses: world.reactions.filter((reaction) => reaction.targetType === "out-there" && reaction.targetId === story.id).length,
      }))
      .sort((a, b) => b.responses - a.responses)
      .slice(0, 4),
    themes: share(themeRows, stories.length).slice(0, 5),
    locations: [...stories]
      .sort((a, b) => time(b.createdAt) - time(a.createdAt))
      .flatMap((story) => {
        const location = members.find((member) => member.id === story.authorId)?.location;
        return location ? [{ location, when: story.createdAt }] : [];
      })
      .filter((item, index, list) => list.findIndex((other) => other.location === item.location) === index)
      .slice(0, 5),
    activities: share(activityRows, stories.length).slice(0, 5),
    openQuestions: questions,
    unanswered: questions.filter((post) => replyCount(post.id) === 0),
    activeConversations: [...campfire]
      .map((post) => ({ post, replies: replyCount(post.id) }))
      .sort((a, b) => b.replies - a.replies)
      .slice(0, 4),
  };
}

export function memberDossier(profile: Profile, world: World, seeds: Seed[], waypoints: Waypoint[]) {
  const names = new Map(world.profiles.map((item) => [item.id, item.displayName]));
  return {
    profile,
    lastActive: lastActiveAt(profile, world),
    waypoints: world.memberships
      .filter((item) => item.userId === profile.id)
      .map((item) => waypoints.find((waypoint) => waypoint.id === item.waypointId))
      .filter((item): item is Waypoint => Boolean(item)),
    seeds: world.userSeeds
      .filter((item) => item.userId === profile.id)
      .map((item) => ({ ...item, seed: seeds.find((seed) => seed.id === item.seedId) })),
    stories: world.stories.filter((story) => story.authorId === profile.id),
    campfire: world.campfire.filter((post) => post.authorId === profile.id),
    swaps: world.connections
      .filter((item) => item.fromUserId === profile.id || item.toUserId === profile.id)
      .map((item) => ({
        ...item,
        withName: names.get(item.fromUserId === profile.id ? item.toUserId : item.fromUserId) ?? "Someone",
      })),
    partnerships: world.partnerships
      .filter((item) => item.seekerId === profile.id || item.partnerId === profile.id)
      .map((item) => ({
        ...item,
        withName: names.get(item.seekerId === profile.id ? item.partnerId ?? "" : item.seekerId) ?? "Open",
        seedTitle: seeds.find((seed) => seed.id === item.seedId)?.title ?? "A seed",
      })),
  };
}

export type Dossier = ReturnType<typeof memberDossier>;

export function countFor(items: UserSeed[], userId: string, status?: UserSeed["status"]) {
  return items.filter((item) => item.userId === userId && (!status || item.status === status)).length;
}
