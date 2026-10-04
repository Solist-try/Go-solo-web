import { SEEDS } from "@/lib/catalog";
import type { CampfirePost, Membership, OutTherePost, Seed, SeedCategory, UserSeed } from "@/lib/types";

const interestCategory: Record<string, SeedCategory> = {
  travel: "adventure",
  walking: "adventure",
  nature: "wellbeing",
  creativity: "creativity",
  books: "growth",
  "home-diy": "home",
  cooking: "home",
  "personal-growth": "growth",
  community: "connection",
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
    return {
      title: "Go out there",
      body: current
        ? `When you try “${current.title}”, bring the story back. What you expected and what happened both belong.`
        : "When you try the thing, bring the story back. Expectation and reality both belong.",
      href: current ? `/out-there/new?seed=${current.id}` : "/out-there/new",
      cta: "Share an experience",
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
    body: "The loop begins again. What else have you been postponing?",
    href: "/seeds",
    cta: "Plant another seed",
  };
}
