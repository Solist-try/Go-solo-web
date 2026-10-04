import { SEEDS, WAYPOINTS } from "@/lib/catalog";
import { sortByNewest } from "@/lib/format";
import type { CampfirePost, OutTherePost, Profile, Seed, Waypoint, World } from "@/lib/types";
import { type AdminState, campfireFlags, storyFlags } from "@/lib/admin/state";

function slugify(value: string) {
  const slug = value
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/(^-|-$)/g, "");
  return slug || "waypoint";
}

export function resolveSeeds(admin: AdminState, includeArchived = false): Seed[] {
  const merged = [...SEEDS, ...admin.customSeeds].map((seed) => {
    const edit = admin.seedEdits[seed.id] ?? {};
    return {
      ...seed,
      ...edit,
      id: seed.id,
      difficulty: edit.difficulty ?? seed.difficulty ?? "gentle",
      waypoints: edit.waypoints ?? seed.waypoints,
    };
  });
  const unique = new Map(merged.map((seed) => [seed.id, seed]));
  return [...unique.values()].filter((seed) => includeArchived || !admin.archivedSeedIds.includes(seed.id));
}

export function resolveWaypoints(admin: AdminState, includeArchived = false): Waypoint[] {
  const merged = [...WAYPOINTS, ...admin.customWaypoints].map((waypoint) => {
    const edit = admin.waypointEdits[waypoint.id] ?? {};
    const name = edit.name ?? waypoint.name;
    return {
      ...waypoint,
      ...edit,
      id: waypoint.id,
      name,
      slug: edit.slug || waypoint.slug || slugify(name),
      focus: edit.focus ?? waypoint.focus,
    };
  });
  const unique = new Map(merged.map((waypoint) => [waypoint.id, waypoint]));
  return [...unique.values()].filter(
    (waypoint) => includeArchived || !admin.archivedWaypointIds.includes(waypoint.id),
  );
}

export function applyMembers(profiles: Profile[], admin: AdminState): Profile[] {
  const removed = new Set(admin.removedMemberIds);
  return profiles
    .filter((profile) => !removed.has(profile.id))
    .map((profile) => ({
      ...profile,
      ...admin.memberEdits[profile.id],
      role: profile.role ?? "member",
      status: admin.memberStatus[profile.id] ?? profile.status ?? "active",
    }));
}

function visibleRank(flags: { pinned?: boolean; featured?: boolean }) {
  if (flags.pinned) return 0;
  if (flags.featured) return 1;
  return 2;
}

function presentStories(stories: OutTherePost[], admin: AdminState) {
  const visible = sortByNewest(stories)
    .map((story) => storyFlags(admin, story))
    .filter((story) => !story.removed && !story.hidden);
  return visible.sort((a, b) => visibleRank(a) - visibleRank(b));
}

function presentCampfire(posts: CampfirePost[], admin: AdminState) {
  const visible = sortByNewest(posts)
    .map((post) => campfireFlags(admin, post))
    .filter((post) => !post.removed && !post.hidden);
  return visible.sort((a, b) => visibleRank(a) - visibleRank(b));
}

export function presentWorld(world: World, admin: AdminState): World {
  const profiles = applyMembers(world.profiles, admin);
  const ids = new Set(profiles.map((profile) => profile.id));
  const stories = presentStories(
    world.stories.filter((story) => ids.has(story.authorId)),
    admin,
  );
  const campfire = presentCampfire(
    world.campfire.filter((post) => ids.has(post.authorId)),
    admin,
  );
  const hiddenStories = new Set(world.stories.map((story) => story.id).filter((id) => !stories.some((story) => story.id === id)));
  const hiddenFire = new Set(world.campfire.map((post) => post.id).filter((id) => !campfire.some((post) => post.id === id)));
  return {
    ...world,
    profiles,
    stories,
    campfire,
    comments: world.comments.filter((comment) => {
      if (!ids.has(comment.authorId)) return false;
      if (comment.targetType === "out-there" && hiddenStories.has(comment.targetId)) return false;
      if (comment.targetType === "campfire" && hiddenFire.has(comment.targetId)) return false;
      return true;
    }),
    userSeeds: world.userSeeds.filter((seed) => ids.has(seed.userId)),
    memberships: world.memberships.filter((item) => ids.has(item.userId)),
    partnerships: world.partnerships.filter(
      (item) => ids.has(item.seekerId) && (!item.partnerId || ids.has(item.partnerId)),
    ),
    offers: world.offers.filter((item) => ids.has(item.userId)),
    requests: world.requests.filter((item) => ids.has(item.userId)),
    connections: world.connections.filter((item) => ids.has(item.fromUserId) && ids.has(item.toUserId)),
    reactions: world.reactions.filter((item) => ids.has(item.userId)),
  };
}

export function newSeedId() {
  return `seed-${crypto.randomUUID().slice(0, 8)}`;
}

export function newWaypointId(name: string) {
  const base = slugify(name);
  return `${base}-${crypto.randomUUID().slice(0, 4)}`;
}
