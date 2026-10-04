import { SEEDS } from "@/lib/catalog";
import type { CampfirePost, Membership, OutTherePost, Seed, SeedCategory, UserSeed } from "@/lib/types";

const interestCategory: Record<string, SeedCategory> = {
  travel: "adventure",
  walking: "adventure",
  nature: "wellbeing",
  creativity: "creativity",
  books: "growth",
  diy: "home",
  cooking: "home",
  "personal-growth": "growth",
  learning: "growth",
};

export type NextStep = {
  title: string;
  body: string;
  href: string;
  cta: string;
};

export function suggestNext(input: {
  userId: string;
  interests: string[];
  userSeeds: UserSeed[];
  stories: OutTherePost[];
  campfire: CampfirePost[];
  memberships: Membership[];
  seeds?: Seed[];
}): NextStep {
  const seeds = input.seeds ?? SEEDS;
  const active = input.userSeeds.filter(
    (seed) => seed.userId === input.userId && seed.status === "active",
  );
  const myStories = input.stories.filter((story) => story.authorId === input.userId);
  const myFire = input.campfire.filter((post) => post.authorId === input.userId);
  const myPoints = input.memberships.filter((membership) => membership.userId === input.userId);

  if (active.length === 0) {
    const category = input.interests
      .map((interest) => interestCategory[interest])
      .find(Boolean);
    const match =
      seeds.find((seed) => seed.category === category && seed.kind === "practice") ?? seeds[0];
    return {
      title: "Find a seed",
      body: match
        ? `One small opening: ${match.title}. You do not have to do it well.`
        : "One small action is enough to make the week feel bigger.",
      href: match ? `/seeds/${match.id}` : "/seeds",
      cta: "Begin with this",
    };
  }

  if (myStories.length === 0) {
    const current = seeds.find((seed) => seed.id === active[0]?.seedId);
    const atHome = current && ["home", "practical-life", "wellbeing"].includes(current.category);
    return {
      title: atHome ? "Bring the ordinary day back" : "Go out there",
      body: current
        ? `When you try “${current.title}”, tell the truth about it. A trip and a Tuesday both belong.`
        : "When you try the thing, tell the truth about it. A trip and a Tuesday both belong.",
      href: current ? `/out-there/new?seed=${current.id}` : "/out-there/new",
      cta: "Share what happened",
    };
  }

  if (myFire.length === 0) {
    return {
      title: "Return to the campfire",
      body: "You do not have to perform. Sit down and say what is actually on your mind.",
      href: "/campfire",
      cta: "Pull up a chair",
    };
  }

  if (myPoints.length === 0) {
    return {
      title: "Choose a waypoint",
      body: "Find people exploring a similar stretch of life, then keep going.",
      href: "/waypoints",
      cta: "See waypoints",
    };
  }

  return {
    title: "Try something new",
    body: "The loop begins again. It might be a neighbourhood, or it might be the kitchen.",
    href: "/seeds",
    cta: "Plant another seed",
  };
}

export const EXPANSION_ACTIONS: NextStep[] = [
  {
    title: "Visit a new neighbourhood",
    body: "Walk a street you usually only pass through.",
    href: "/seeds/walk-unknown",
    cta: "Begin",
  },
  {
    title: "Try a local workshop",
    body: "Say yes to one listing, even if nobody else's calendar is free.",
    href: "/seeds/local-yes",
    cta: "Begin",
  },
  {
    title: "Visit a museum",
    body: "Stay until one sentence arrives, then leave with it.",
    href: "/seeds/museum-sentence",
    cta: "Begin",
  },
];

export const EVERYDAY_ACTIONS: NextStep[] = [
  {
    title: "Create a morning routine",
    body: "One kinder setup, used for seven days.",
    href: "/seeds/morning-system",
    cta: "Begin",
  },
  {
    title: "Review your budget",
    body: "An hour with what comes in and what must go out.",
    href: "/seeds/budget-month",
    cta: "Begin",
  },
  {
    title: "Cook a new recipe",
    body: "Make a meal you have been saving for company.",
    href: "/seeds/meal-for-you",
    cta: "Begin",
  },
  {
    title: "Reorganise a room",
    body: "Move one thing so the room fits the life you have.",
    href: "/seeds/room-for-this-life",
    cta: "Begin",
  },
];
