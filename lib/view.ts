import {
  communityCampfire,
  communityComments,
  communityConnections,
  communityMemberships,
  communityOffers,
  communityPartnerships,
  communityProfiles,
  communityReactions,
  communityRequests,
  communityStories,
  communityUserSeeds,
} from "@/lib/community";
import { sortByNewest } from "@/lib/format";
import { emptyWorld, type PersistedState, type World } from "@/lib/types";

export function buildView(persisted: PersistedState, includeCommunity: boolean): World {
  if (!includeCommunity) {
    return {
      profiles: persisted.profiles,
      userSeeds: persisted.userSeeds,
      memberships: persisted.memberships,
      partnerships: persisted.partnerships,
      offers: persisted.offers,
      requests: persisted.requests,
      connections: persisted.connections,
      stories: sortByNewest(persisted.stories),
      campfire: sortByNewest(persisted.campfire),
      comments: persisted.comments,
      reactions: persisted.reactions,
      notifications: persisted.notifications,
    };
  }

  const world: World = {
    ...emptyWorld(),
    profiles: [...communityProfiles, ...persisted.profiles],
    userSeeds: [...communityUserSeeds, ...persisted.userSeeds],
    memberships: [...communityMemberships, ...persisted.memberships],
    partnerships: [...communityPartnerships, ...persisted.partnerships],
    offers: [...communityOffers, ...persisted.offers],
    requests: [...communityRequests, ...persisted.requests],
    connections: [...communityConnections, ...persisted.connections],
    stories: sortByNewest([...communityStories, ...persisted.stories]),
    campfire: sortByNewest([...communityCampfire, ...persisted.campfire]),
    comments: [...communityComments, ...persisted.comments],
    reactions: [...communityReactions, ...persisted.reactions],
    notifications: persisted.notifications,
  };

  return world;
}

export function visibleSeeking<T extends { status: string; seedId: string; seekerId: string; partnerId: string | null }>(
  partnerships: T[],
) {
  return partnerships.filter((partnership) => {
    if (partnership.status !== "seeking") return false;
    const taken = partnerships.some(
      (other) =>
        other.status === "matched" &&
        other.seedId === partnership.seedId &&
        (other.partnerId === partnership.seekerId || other.seekerId === partnership.seekerId),
    );
    return !taken;
  });
}
