import type {
  CampfirePost,
  MemberStatus,
  OutTherePost,
  Profile,
  Seed,
  SeedDifficulty,
  Waypoint,
  WaypointColour,
} from "@/lib/types";

export const STEWARD_EMAIL = "steward@gosolo.example";
export const STEWARD_PASSWORD = "gosolo-steward";
export const STEWARD_ID = "steward";
/** SHA-256 of `gosolo-demo:${STEWARD_PASSWORD}`, matching the preview password hash. */
export const STEWARD_PASSWORD_HASH =
  "a0eee80b8dcb246ad99dc5acf49a297cdcf4bdd7e80f1df9b26f7d6e711bd0d6";

export const DIFFICULTIES: { id: SeedDifficulty; label: string }[] = [
  { id: "gentle", label: "Gentle" },
  { id: "steady", label: "Steady" },
  { id: "brave", label: "Brave" },
];

export const COLOURS: { id: WaypointColour; label: string }[] = [
  { id: "sage", label: "Sage" },
  { id: "clay", label: "Clay" },
  { id: "gold", label: "Gold" },
  { id: "mist", label: "Mist" },
];

export type EmailTemplate = {
  id: string;
  name: string;
  subject: string;
  body: string;
};

export type ComingSoonCard = {
  title: string;
  body: string;
};

export type HowStep = {
  mark: string;
  name: string;
  body: string;
  lines: string[];
  linesLabel: string;
  href: string;
  hrefLabel: string;
};

export type SiteContent = {
  heroTagline: string;
  heroEyebrow: string;
  heroTitle: string;
  heroSubhead: string;
  heroSupport: string;
  heroLede: string;
  heroPrimary: string;
  heroSecondary: string;
  phrases: string[];
  philosophyTitle: string;
  philosophyParagraphs: string[];
  philosophyClose: string;
  belief: string;
  beliefSecond: string;
  whatTitle: string;
  whatBody: string;
  waitingIntro: string;
  waitingFor: string[];
  whatBridge: string;
  togetherIntro: string;
  together: string[];
  comeHereTitle: string;
  comeHere: string[];
  howTitle: string;
  howIntro: string;
  steps: HowStep[];
  seedsIntro: string;
  manifesto: string[];
  comingSoonIntro: string;
  comingSoon: ComingSoonCard[];
  emails: EmailTemplate[];
  founderName: string;
  founderPhoto: string;
  founderBio: string;
  founderLetter: string[];
  founderEmail: string;
};

export type HouseLetter = {
  id: string;
  name: string;
  email: string;
  body: string;
  createdAt: string;
};

export type PostModeration = {
  hidden?: boolean;
  pinned?: boolean;
  featured?: boolean;
  locked?: boolean;
  removed?: boolean;
};

export type MemberEdit = Partial<
  Pick<Profile, "displayName" | "bio" | "location" | "intentions" | "interests">
>;

export type DeskReport = {
  id: string;
  targetType: "out-there" | "campfire" | "member";
  targetId: string;
  reason: string;
  status: "open" | "resolved" | "dismissed";
  createdAt: string;
};

export type WarningNote = {
  id: string;
  userId: string;
  note: string;
  createdAt: string;
};

export type JournalEntry = {
  id: string;
  at: string;
  text: string;
};

export type DeskSettings = {
  note: string;
  warnTemplate: string;
};

export type AdminState = {
  content: SiteContent;
  seedEdits: Record<string, Partial<Seed>>;
  customSeeds: Seed[];
  archivedSeedIds: string[];
  featuredSeedIds: string[];
  waypointEdits: Record<string, Partial<Waypoint>>;
  customWaypoints: Waypoint[];
  archivedWaypointIds: string[];
  featuredWaypointIds: string[];
  storyModeration: Record<string, PostModeration>;
  campfireModeration: Record<string, PostModeration>;
  memberEdits: Record<string, MemberEdit>;
  memberStatus: Record<string, MemberStatus>;
  removedMemberIds: string[];
  warnings: WarningNote[];
  reports: DeskReport[];
  journal: JournalEntry[];
  letters: HouseLetter[];
  settings: DeskSettings;
};

export function defaultContent(): SiteContent {
  return {
    heroTagline: "Go Solo. Not Alone.",
    heroEyebrow: "A calm home for an independent life",
    heroTitle: "Hello, Vagabond.",
    heroSubhead: "Your life doesn't have to wait.",
    heroSupport: "Try the thing. Take the trip. Learn the skill. Eat the weird food. Paint the room orange.",
    heroLede: "Go Solo helps people explore, connect and grow while living independently.",
    heroPrimary: "Join Go Solo",
    heroSecondary: "Explore Seeds",
    phrases: ["Try the thing.", "Take the trip.", "Paint the room orange."],
    philosophyTitle: "Stop waiting for company before you begin.",
    philosophyParagraphs: [
      "Many people postpone experiences because they are waiting for a partner, for friends, for schedules to align, or for permission.",
      "Go Solo exists for the life that's happening now.",
      "This is not a place to perform.",
      "It is a home base for people who are out there living.",
    ],
    philosophyClose: "You can live alone without being alone.",
    belief: "Living alone is not the problem.",
    beliefSecond: "Living on hold is.",
    whatTitle: "What Is Go Solo?",
    whatBody:
      "Go Solo is a community for people who live independently and want to do more with their lives.",
    waitingIntro: "Many of us postpone experiences while waiting for:",
    waitingFor: ["The right person", "The right timing", "Matching schedules", "More confidence"],
    whatBridge: "Go Solo helps people stop waiting.",
    togetherIntro: "Together, members:",
    together: ["Try new things", "Share what happened", "Support each other", "Build bigger lives"],
    comeHereTitle: "People come here to",
    comeHere: [
      "Try new experiences",
      "Build confidence",
      "Find accountability",
      "Exchange skills",
      "Share real-world experiences",
      "Connect with people on similar journeys",
    ],
    howTitle: "How Go Solo Works",
    howIntro: "Life gets bigger one small step at a time.",
    steps: [
      {
        mark: "🌱",
        name: "Find a Seed",
        body: "Choose something new to try.",
        linesLabel: "Examples",
        lines: ["Learn a skill", "Take a solo day trip", "Start a new routine"],
        href: "/seeds",
        hrefLabel: "Browse seeds",
      },
      {
        mark: "🚶",
        name: "Go Out There",
        body: "Try it in real life.",
        linesLabel: "",
        lines: ["The goal isn't perfection.", "The goal is experience."],
        href: "/out-there",
        hrefLabel: "Read what people tried",
      },
      {
        mark: "🔥",
        name: "Return to Campfire",
        body: "Share your story.",
        linesLabel: "",
        lines: ["Ask questions.", "Reflect on what happened."],
        href: "/campfire",
        hrefLabel: "Visit the campfire",
      },
      {
        mark: "🧭",
        name: "Visit a Waypoint",
        body: "Connect with people exploring similar parts of life.",
        linesLabel: "",
        lines: [],
        href: "/waypoints",
        hrefLabel: "See the waypoints",
      },
    ],
    seedsIntro:
      "Seeds are small actions that make life bigger. Not goals. Not productivity. Not self-improvement. Possibilities.",
    manifesto: [
      "Go Solo helps people build bigger lives while living independently.",
      "Living alone is not the problem. Living on hold is.",
      "You can live alone without being alone.",
    ],
    comingSoonIntro: "These are on the horizon. They are not open, and they will not ask for your attention yet.",
    comingSoon: [
      {
        title: "Experiences",
        body: "Museum visits, theatre nights, workshops, walks, and day trips. Real rooms, when the time is right.",
      },
      {
        title: "Partnerships",
        body: "Solo-friendly restaurants, museums, travel, and learning. Opportunities with no plus-one required.",
      },
      {
        title: "First Night Kits",
        body: "A moving kit. A starting-over kit. A travel kit. Companions for the first night of a new chapter.",
      },
    ],
    founderName: "",
    founderPhoto: "",
    founderBio:
      "I live on my own. I started Go Solo because I was tired of treating that as a reason to postpone the rest of life.",
    founderLetter: [
      "Hello. If you are reading this, you have found the person behind the place.",
      "I still answer what comes in. There is no team standing between a note and a reply, and there is no one else tending the campfire when the day is quiet.",
      "Some parts of a life are better with company. Many parts are perfectly good to begin alone. I built this so those two things could sit in the same room.",
    ],
    founderEmail: "",
    emails: [
      {
        id: "welcome",
        name: "Welcome",
        subject: "Your life doesn't have to wait",
        body: "Hello, Vagabond.\n\nGo Solo is a home base you can leave, and return to. Start with one small seed.\n\nGo Solo. Not Alone.",
      },
      {
        id: "verify",
        name: "Email verification",
        subject: "Confirm your email",
        body: "Confirm this address so you can come back to Go Solo after you have been out there.",
      },
      {
        id: "reset",
        name: "Password reset",
        subject: "Choose a new password",
        body: "Someone asked to reset the password for this Go Solo account. If that was you, choose a new one. If it was not, you can ignore this letter.",
      },
      {
        id: "return",
        name: "A quiet return",
        subject: "The chair is still here",
        body: "No streak. No score. If something is calling, there is a seed for it, and a campfire when you come back.",
      },
    ],
  };
}

export function defaultAdmin(): AdminState {
  return {
    content: defaultContent(),
    seedEdits: {},
    customSeeds: [],
    archivedSeedIds: [],
    featuredSeedIds: [],
    waypointEdits: {},
    customWaypoints: [],
    archivedWaypointIds: [],
    featuredWaypointIds: [],
    storyModeration: {},
    campfireModeration: {},
    memberEdits: {},
    memberStatus: {},
    removedMemberIds: [],
    warnings: [],
    reports: [
      {
        id: "report-address",
        targetType: "campfire",
        targetId: "fire-asha-permission",
        reason: "A reply asks where someone lives. It can stay if the address comes out.",
        status: "open",
        createdAt: "2026-10-03T11:00:00.000Z",
      },
      {
        id: "report-museum",
        targetType: "out-there",
        targetId: "story-mira",
        reason: "Someone asked if this story should be pinned for people just beginning.",
        status: "open",
        createdAt: "2026-10-02T16:00:00.000Z",
      },
    ],
    journal: [],
    letters: [],
    settings: {
      note: "Keep the room habitable. Measure action, not performance.",
      warnTemplate: "A steward read what you shared and is asking for a gentler version. Nothing here needs to expose someone's private life.",
    },
  };
}

function asRecord<T>(value: unknown): Record<string, T> {
  if (!value || typeof value !== "object" || Array.isArray(value)) return {};
  return value as Record<string, T>;
}

export function mergeAdmin(stored?: Partial<AdminState> | null): AdminState {
  const base = defaultAdmin();
  if (!stored) return base;
  const content = { ...base.content, ...(stored.content ?? {}) };
  content.phrases = stored.content?.phrases?.length ? stored.content.phrases : base.content.phrases;
  content.philosophyParagraphs = stored.content?.philosophyParagraphs?.length
    ? stored.content.philosophyParagraphs
    : base.content.philosophyParagraphs;
  const storedSteps = stored.content?.steps?.length ? stored.content.steps : base.content.steps;
  content.steps = storedSteps.map((step) => ({
    mark: step.mark,
    name: step.name,
    body: step.body,
    lines: step.lines ?? [],
    linesLabel: step.linesLabel ?? "",
    href: step.href ?? "",
    hrefLabel: step.hrefLabel ?? "",
  }));
  content.waitingFor = stored.content?.waitingFor?.length ? stored.content.waitingFor : base.content.waitingFor;
  content.together = stored.content?.together?.length ? stored.content.together : base.content.together;
  content.comeHere = stored.content?.comeHere?.length ? stored.content.comeHere : base.content.comeHere;
  content.manifesto = stored.content?.manifesto?.length ? stored.content.manifesto : base.content.manifesto;
  content.comingSoon = stored.content?.comingSoon?.length ? stored.content.comingSoon : base.content.comingSoon;
  content.emails = stored.content?.emails?.length ? stored.content.emails : base.content.emails;
  content.founderLetter = stored.content?.founderLetter?.length
    ? stored.content.founderLetter
    : base.content.founderLetter;
  return {
    ...base,
    ...stored,
    content,
    letters: stored.letters ?? [],
    settings: { ...base.settings, ...(stored.settings ?? {}) },
    seedEdits: asRecord(stored.seedEdits),
    customSeeds: stored.customSeeds ?? [],
    archivedSeedIds: stored.archivedSeedIds ?? [],
    featuredSeedIds: stored.featuredSeedIds ?? [],
    waypointEdits: asRecord(stored.waypointEdits),
    customWaypoints: stored.customWaypoints ?? [],
    archivedWaypointIds: stored.archivedWaypointIds ?? [],
    featuredWaypointIds: stored.featuredWaypointIds ?? [],
    storyModeration: asRecord(stored.storyModeration),
    campfireModeration: asRecord(stored.campfireModeration),
    memberEdits: asRecord(stored.memberEdits),
    memberStatus: asRecord(stored.memberStatus),
    removedMemberIds: stored.removedMemberIds ?? [],
    warnings: stored.warnings ?? [],
    reports: stored.reports ?? base.reports,
    journal: stored.journal ?? [],
  };
}

export function note(admin: AdminState, text: string): AdminState {
  return {
    ...admin,
    journal: [{ id: crypto.randomUUID(), at: new Date().toISOString(), text }, ...admin.journal].slice(0, 40),
  };
}

export function moderationFor(
  admin: AdminState,
  kind: "story" | "campfire",
  id: string,
): PostModeration {
  return (kind === "story" ? admin.storyModeration[id] : admin.campfireModeration[id]) ?? {};
}

export function storyFlags(admin: AdminState, story: OutTherePost): OutTherePost & PostModeration {
  return { ...story, ...admin.storyModeration[story.id] };
}

export function campfireFlags(admin: AdminState, post: CampfirePost): CampfirePost & PostModeration {
  return { ...post, ...admin.campfireModeration[post.id] };
}
