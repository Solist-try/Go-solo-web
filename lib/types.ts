export const INTENTIONS = [
  { id: "connection", label: "More connection" },
  { id: "adventure", label: "More adventure" },
  { id: "accountability", label: "Accountability" },
  { id: "confidence", label: "Building confidence" },
  { id: "starting-over", label: "Starting over" },
  { id: "first-time-alone", label: "Living alone for the first time" },
  { id: "meeting-people", label: "Meeting people" },
  { id: "curiosity", label: "Curiosity" },
] as const;

export const INTERESTS = [
  { id: "travel", label: "Travel" },
  { id: "books", label: "Books" },
  { id: "creativity", label: "Creativity" },
  { id: "nature", label: "Nature" },
  { id: "learning", label: "Learning" },
  { id: "cooking", label: "Cooking" },
  { id: "walking", label: "Walking" },
  { id: "diy", label: "DIY" },
  { id: "personal-growth", label: "Personal Growth" },
] as const;

export const SEED_CATEGORIES = [
  {
    id: "adventure",
    label: "Adventure",
    line: "Go somewhere you have been postponing.",
  },
  {
    id: "connection",
    label: "Connection",
    line: "Reach toward someone without turning it into a performance.",
  },
  {
    id: "creativity",
    label: "Creativity",
    line: "Make something that does not need an audience.",
  },
  {
    id: "home",
    label: "Home",
    line: "Shape the place where your life actually happens.",
  },
  {
    id: "growth",
    label: "Growth",
    line: "Begin the thing at the size of one hour.",
  },
  {
    id: "wellbeing",
    label: "Wellbeing",
    line: "Give your days a little more room to breathe.",
  },
  {
    id: "practical-life",
    label: "Practical Life",
    line: "Meals, money, repairs, and the routines that hold a week together.",
  },
  {
    id: "relationships",
    label: "Relationships",
    line: "A friend, a coffee, a local group, a way back into community.",
  },
] as const;

export const CAMPFIRE_SECTIONS = [
  { id: "general", label: "General", line: "Whatever is actually in the room." },
  { id: "growing", label: "Growing", line: "The stretch of trying something new." },
  { id: "solo-living", label: "Solo Living", line: "The practical, tender work of a life of your own." },
  { id: "deep-thoughts", label: "Deep Thoughts", line: "Slower sentences. Nothing to solve." },
] as const;

export const CAMPFIRE_KINDS = [
  { id: "question", label: "Question" },
  { id: "thought", label: "Thought" },
  { id: "reflection", label: "Reflection" },
  { id: "daily-life", label: "Daily life" },
  { id: "story", label: "Story" },
  { id: "celebration", label: "Celebration" },
  { id: "challenge", label: "Challenge" },
] as const;

export const REACTIONS = [
  { id: "inspired", label: "Inspired me", phrase: "felt inspired" },
  { id: "relate", label: "I relate", phrase: "relates" },
  { id: "perspective", label: "Interesting perspective", phrase: "found another perspective" },
] as const;

export const WOULD_AGAIN = [
  { id: "yes", label: "Yes" },
  { id: "maybe", label: "Maybe, differently" },
  { id: "not-this-way", label: "Not in this way" },
] as const;

export type IntentionId = (typeof INTENTIONS)[number]["id"];
export type InterestId = (typeof INTERESTS)[number]["id"];
export type SeedCategory = (typeof SEED_CATEGORIES)[number]["id"];
export type SeedKind = "practice" | "same" | "skill-swap";
export type CampfireSection = (typeof CAMPFIRE_SECTIONS)[number]["id"];
export type CampfireKind = (typeof CAMPFIRE_KINDS)[number]["id"];
export type ReactionKind = (typeof REACTIONS)[number]["id"];
export type WouldAgain = (typeof WOULD_AGAIN)[number]["id"];
export type SeedStatus = "active" | "resting" | "completed";
export type TargetType = "out-there" | "campfire";
export type MemberRole = "member" | "admin";
export type MemberStatus = "active" | "suspended" | "deactivated";
export type SeedDifficulty = "gentle" | "steady" | "brave";
export type WaypointColour = "sage" | "clay" | "gold" | "mist";

export type LifeSeed = {
  id: string;
  name: string;
  status: "active" | "resting";
  since: string;
  lookingForSupport: boolean;
};

export const SUPPORT_WITH = [
  { id: "accountability", label: "Accountability" },
  { id: "confidence", label: "Confidence" },
  { id: "starting-over", label: "Starting over" },
  { id: "connection", label: "Connection" },
  { id: "practical-life", label: "Practical life" },
  { id: "medical", label: "Medical support" },
  { id: "wellbeing", label: "Wellbeing" },
  { id: "career", label: "Career change" },
] as const;

export const CHECK_IN_FREQUENCIES = [
  { id: "daily", label: "Daily" },
  { id: "several-weekly", label: "Several times per week" },
  { id: "weekly", label: "Weekly" },
  { id: "fortnightly", label: "Every two weeks" },
  { id: "monthly", label: "Monthly" },
  { id: "as-needed", label: "As needed" },
] as const;

export const CHECK_IN_STYLES = [
  { id: "messages", label: "Messages" },
  { id: "voice", label: "Voice Notes" },
  { id: "video", label: "Video Calls" },
  { id: "email", label: "Email" },
  { id: "any", label: "Any Format" },
] as const;

export type Profile = {
  id: string;
  email: string;
  displayName: string;
  bio: string;
  location: string;
  avatarUrl: string;
  intentions: string[];
  interests: string[];
  onboardingComplete: boolean;
  emailVerified: boolean;
  showLocation: boolean;
  showWaypoints: boolean;
  notifyReplies: boolean;
  notifyWaypoints: boolean;
  notifyCheckins: boolean;
  createdAt: string;
  role?: MemberRole;
  status?: MemberStatus;
  growing?: LifeSeed[];
  helpGrowing?: string[];
  helpPlant?: string[];
  helpPlantNote?: string;
  supportWith?: string[];
  checkInFrequency?: string;
  checkInStyle?: string;
};

export type Account = {
  id: string;
  email: string;
  passwordHash: string;
  resetToken: string | null;
};

export type Waypoint = {
  id: string;
  slug: string;
  name: string;
  summary: string;
  description: string;
  focus: string[];
  colour?: WaypointColour;
  icon?: string;
};

export type Seed = {
  id: string;
  title: string;
  description: string;
  prompt: string;
  category: SeedCategory;
  kind: SeedKind;
  timeframe: string;
  waypoints: string[];
  difficulty?: SeedDifficulty;
};

export type CheckIn = {
  id: string;
  at: string;
  note: string;
  userId?: string;
};

export type UserSeed = {
  id: string;
  userId: string;
  seedId: string;
  status: SeedStatus;
  goal: string;
  startedAt: string;
  checkIns: CheckIn[];
};

export type Membership = {
  userId: string;
  waypointId: string;
  joinedAt: string;
};

export type Partnership = {
  id: string;
  seedId: string;
  seekerId: string;
  partnerId: string | null;
  goal: string;
  status: "seeking" | "matched";
  createdAt: string;
  checkIns: CheckIn[];
};

export type SkillOffer = {
  id: string;
  userId: string;
  skill: string;
  description: string;
  createdAt: string;
};

export type SkillRequest = {
  id: string;
  userId: string;
  skill: string;
  description: string;
  createdAt: string;
};

export type SkillConnection = {
  id: string;
  fromUserId: string;
  toUserId: string;
  offerId?: string;
  requestId?: string;
  note: string;
  createdAt: string;
};

export type OutTherePost = {
  id: string;
  authorId: string;
  title: string;
  whatDidYouDo: string;
  expecting: string;
  actuallyHappened: string;
  wouldDoAgain: WouldAgain;
  seedId?: string;
  waypointId?: string;
  createdAt: string;
  hidden?: boolean;
  pinned?: boolean;
  featured?: boolean;
};

export type CampfirePost = {
  id: string;
  authorId: string;
  section: CampfireSection;
  kind: CampfireKind;
  title: string;
  body: string;
  waypointId?: string;
  seedId?: string;
  outThereId?: string;
  createdAt: string;
  hidden?: boolean;
  pinned?: boolean;
  featured?: boolean;
  locked?: boolean;
};

export type Comment = {
  id: string;
  authorId: string;
  targetType: TargetType;
  targetId: string;
  body: string;
  createdAt: string;
};

export type Reaction = {
  id: string;
  userId: string;
  targetType: TargetType;
  targetId: string;
  kind: ReactionKind;
};

export type AppNotification = {
  id: string;
  userId: string;
  title: string;
  body: string;
  href?: string;
  read: boolean;
  createdAt: string;
};

export type PersistedState = {
  version: 1;
  accounts: Account[];
  profiles: Profile[];
  userSeeds: UserSeed[];
  memberships: Membership[];
  partnerships: Partnership[];
  offers: SkillOffer[];
  requests: SkillRequest[];
  connections: SkillConnection[];
  stories: OutTherePost[];
  campfire: CampfirePost[];
  comments: Comment[];
  reactions: Reaction[];
  notifications: AppNotification[];
};

export type World = {
  profiles: Profile[];
  userSeeds: UserSeed[];
  memberships: Membership[];
  partnerships: Partnership[];
  offers: SkillOffer[];
  requests: SkillRequest[];
  connections: SkillConnection[];
  stories: OutTherePost[];
  campfire: CampfirePost[];
  comments: Comment[];
  reactions: Reaction[];
  notifications: AppNotification[];
};

export function emptyPersisted(): PersistedState {
  return {
    version: 1,
    accounts: [],
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

export function emptyWorld(): World {
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
