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

export type Pillar = {
  name: string;
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
  contentRevision: number;
  heroTagline: string;
  heroEyebrow: string;
  heroTitle: string;
  heroSubhead: string;
  heroSupport: string;
  heroLede: string;
  heroPrimary: string;
  heroSecondary: string;
  phrases: string[];
  balanceIntro: string;
  balanceItems: string[];
  balanceClose: string;
  whoTitle: string;
  who: string[];
  pillars: Pillar[];
  pillarNote: string;
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
  expansionTitle: string;
  expansion: string[];
  everydayTitle: string;
  everyday: string[];
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
    contentRevision: 3,
    heroTagline: "Go Solo. Not Alone.",
    heroEyebrow: "",
    heroTitle: "Hello, Vagabond.",
    heroSubhead: "Your life doesn't have to wait.",
    heroSupport: "Go Solo helps people build lives that work, whether or not somebody else shows up.",
    heroLede: "",
    heroPrimary: "Join Go Solo",
    heroSecondary: "Explore Seeds",
    phrases: ["Try the thing.", "Take the trip.", "Paint the room orange."],
    balanceIntro: "Sometimes the thing is",
    balanceItems: [
      "Taking a trip.",
      "Going to a bar alone.",
      "Cooking for one.",
      "Learning a practical skill.",
      "Creating a routine.",
      "Starting over.",
      "Making a new friend.",
    ],
    balanceClose: "Both belong here.",
    whoTitle: "This is for people who are",
    who: [
      "Living alone",
      "Living independently",
      "Starting over",
      "Relocating",
      "Experiencing life transitions",
      "Building new routines",
      "Creating social lives from scratch",
      "Learning how to do things on their own",
    ],
    pillars: [
      { name: "Support", body: "Encouragement, practical advice, and a place to come back to." },
      { name: "Confidence", body: "Practice for the things you have been waiting to do on your own." },
      { name: "Action", body: "A small seed, tried in real life, then told truthfully." },
      { name: "Connection", body: "Friends, coffee, a local room, and people on a similar stretch of life." },
      { name: "Expansion", body: "Travel, learning, and a wider life that can grow from a steady week." },
    ],
    pillarNote: "Support comes first. Action comes second. Adventure comes later.",
    philosophyTitle: "A supportive companion.",
    philosophyParagraphs: [
      "Many parts of modern life are designed around pairs, families or groups.",
      "Go Solo is a home base for people building a life that works whether or not somebody else shows up. It offers practical guidance, encouragement, connection, and stories.",
      "Adventure is one expression of independence. Everyday life is another. A trip can belong here. So can cooking for one, a new routine, and a friend you make from scratch.",
    ],
    philosophyClose: "Support comes first. Action comes second. Adventure comes later.",
    belief: "Living alone is not the problem.",
    beliefSecond: "Living on hold is.",
    whatTitle: "What Is Go Solo?",
    whatBody:
      "Go Solo helps people build meaningful lives without relying on the constant availability of partners, family, friends or built-in support systems.",
    waitingIntro:
      "Many parts of modern life are designed around pairs, families or groups. People often postpone life because they:",
    waitingFor: [
      "Have nobody to go with",
      "Do not know how to do something alone",
      "Need encouragement",
      "Need accountability",
      "Need practical advice",
      "Need human connection",
    ],
    whatBridge: "Go Solo helps bridge that gap.",
    togetherIntro: "Together, members:",
    together: ["Try new things", "Share what happened", "Support each other", "Build bigger lives"],
    comeHereTitle: "Come here for",
    comeHere: [
      "A supportive companion",
      "A home base",
      "Practical guidance",
      "Encouragement",
      "Connection",
      "Stories",
    ],
    expansionTitle: "Life expansion",
    expansion: ["Travel", "New experiences", "Learning", "Events", "Exploration", "Creativity", "Adventure"],
    everydayTitle: "Everyday living",
    everyday: [
      "Home management",
      "Routines",
      "Budgeting",
      "Cooking",
      "Friendship",
      "Wellbeing",
      "Time management",
      "Household maintenance",
      "Self-reliance",
      "Starting over",
      "Transition periods",
      "Emotional resilience",
    ],
    howTitle: "How Go Solo Works",
    howIntro: "Life gets bigger one small step at a time.",
    steps: [
      {
        mark: "🌱",
        name: "Find a Seed",
        body: "Choose something new to try.",
        linesLabel: "Examples",
        lines: ["Learn a skill", "Take a solo day trip", "Start a new routine", "Review a month of spending"],
        href: "/seeds",
        hrefLabel: "Browse seeds",
      },
      {
        mark: "🚶",
        name: "Go Out There",
        body: "Try it in real life.",
        linesLabel: "",
        lines: ["The goal isn't perfection.", "The goal is experience.", "A trip counts. A Tuesday routine counts."],
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
      "Seeds help people build confidence and capability. Some are adventures. Some are practical. Some are a way back to other people.",
    manifesto: [
      "Go Solo helps people build lives that work, whether or not somebody else shows up.",
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
  const refreshCopy = (stored.content?.contentRevision ?? 0) < base.content.contentRevision;
  content.phrases = !refreshCopy && stored.content?.phrases?.length ? stored.content.phrases : base.content.phrases;
  if (refreshCopy) {
    content.contentRevision = base.content.contentRevision;
    content.heroSupport = base.content.heroSupport;
    content.heroLede = base.content.heroLede;
    content.balanceIntro = base.content.balanceIntro;
    content.balanceClose = base.content.balanceClose;
    content.whoTitle = base.content.whoTitle;
    content.pillarNote = base.content.pillarNote;
    content.philosophyTitle = base.content.philosophyTitle;
    content.philosophyClose = base.content.philosophyClose;
    content.whatTitle = base.content.whatTitle;
    content.whatBody = base.content.whatBody;
    content.waitingIntro = base.content.waitingIntro;
    content.whatBridge = base.content.whatBridge;
    content.togetherIntro = base.content.togetherIntro;
    content.comeHereTitle = base.content.comeHereTitle;
    content.seedsIntro = base.content.seedsIntro;
  }
  content.philosophyParagraphs =
    !refreshCopy && stored.content?.philosophyParagraphs?.length
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
  content.waitingFor = !refreshCopy && stored.content?.waitingFor?.length ? stored.content.waitingFor : base.content.waitingFor;
  content.together = !refreshCopy && stored.content?.together?.length ? stored.content.together : base.content.together;
  content.comeHere = !refreshCopy && stored.content?.comeHere?.length ? stored.content.comeHere : base.content.comeHere;
  content.balanceItems = !refreshCopy && stored.content?.balanceItems?.length ? stored.content.balanceItems : base.content.balanceItems;
  content.who = !refreshCopy && stored.content?.who?.length ? stored.content.who : base.content.who;
  content.pillars = !refreshCopy && stored.content?.pillars?.length ? stored.content.pillars : base.content.pillars;
  content.expansion = stored.content?.expansion?.length ? stored.content.expansion : base.content.expansion;
  content.everyday = stored.content?.everyday?.length ? stored.content.everyday : base.content.everyday;
  content.manifesto = !refreshCopy && stored.content?.manifesto?.length ? stored.content.manifesto : base.content.manifesto;
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
