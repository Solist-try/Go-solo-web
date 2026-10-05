import type {
  CampfirePost,
  Comment,
  Membership,
  OutTherePost,
  Partnership,
  Profile,
  Reaction,
  SkillConnection,
  SkillOffer,
  SkillRequest,
  UserSeed,
} from "@/lib/types";

/** The room starts empty. Nothing here is invented. */
export const communityProfiles: Profile[] = [];
export const communityMemberships: Membership[] = [];
export const communityUserSeeds: UserSeed[] = [];
export const communityPartnerships: Partnership[] = [];
export const communityOffers: SkillOffer[] = [];
export const communityRequests: SkillRequest[] = [];
export const communityConnections: SkillConnection[] = [];
export const communityStories: OutTherePost[] = [];
export const communityCampfire: CampfirePost[] = [];
export const communityComments: Comment[] = [];
export const communityReactions: Reaction[] = [];
