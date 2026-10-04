import type { Seed, Waypoint } from "@/lib/types";

export const WAYPOINTS: Waypoint[] = [
  {
    id: "independence-lab",
    slug: "independence-lab",
    name: "Independence Lab",
    summary: "Routines, home systems, self-reliance, and confidence.",
    description:
      "A waypoint for the life that happens after the door closes. The small systems, the ordinary courage, and the confidence that comes from trusting your own hands.",
    focus: ["Routines", "Home systems", "Self-reliance", "Confidence"],
  },
  {
    id: "solo-among-others",
    slug: "solo-among-others",
    name: "Solo Among Others",
    summary: "Friendship, belonging, social confidence, and companionship.",
    description:
      "For the stretch between solitude and other people. How to belong without disappearing, and how to be out in the world without waiting for a plus-one.",
    focus: ["Friendship", "Belonging", "Social confidence", "Companionship"],
  },
  {
    id: "emotional-clarity",
    slug: "emotional-clarity",
    name: "Emotional Clarity",
    summary: "Reflection, identity, change, and growth.",
    description:
      "A slower circle. Identity, change, and the sentences that only arrive when you stop performing them. Nothing here needs to be optimized.",
    focus: ["Reflection", "Identity", "Change", "Growth"],
  },
];

export const SEEDS: Seed[] = [
  {
    id: "walk-unknown",
    title: "Walk a street you don't know",
    description: "A small adventure that asks nothing of anyone else.",
    prompt:
      "Choose a neighborhood you always pass through. Walk it slowly for forty minutes. Notice one thing you would tell a friend.",
    category: "adventure",
    kind: "practice",
    timeframe: "An afternoon",
    waypoints: ["solo-among-others"],
  },
  {
    id: "ticket-for-one",
    title: "Buy a ticket for one",
    description: "Stop waiting for a date that makes the outing make sense.",
    prompt:
      "Book something small that happens this month: a train, a film, a talk. Go alone on purpose.",
    category: "adventure",
    kind: "practice",
    timeframe: "One outing",
    waypoints: ["emotional-clarity", "solo-among-others"],
  },
  {
    id: "local-yes",
    title: "Say yes to a listing",
    description: "One local yes, even if nobody else's calendar is free.",
    prompt:
      "Pick one local event you would usually skip because no one else is free. Put it on the calendar.",
    category: "adventure",
    kind: "practice",
    timeframe: "This week",
    waypoints: ["solo-among-others"],
  },
  {
    id: "weekly-hello",
    title: "A weekly hello",
    description: "One person. One recurring note. No performance.",
    prompt:
      "Choose one person and a small recurring check-in. Support, accountability, mutual, empowerment. Nothing more elaborate than showing up.",
    category: "connection",
    kind: "same",
    timeframe: "Weekly",
    waypoints: ["solo-among-others", "independence-lab"],
  },
  {
    id: "neighbor",
    title: "A conversation without an agenda",
    description: "Be a person in the world for a few minutes.",
    prompt:
      "Speak to someone nearby — a neighbor, a barista, a person at the same class — with no plan to become friends. Just be there.",
    category: "connection",
    kind: "practice",
    timeframe: "Once",
    waypoints: ["solo-among-others"],
  },
  {
    id: "skill-swap",
    title: "Trade a skill",
    description: "Offer what you know. Ask for what you have been postponing.",
    prompt:
      "Offer something you know well enough to share for an hour. Ask for something you have been waiting to learn. Photography, gardening, DIY, cooking, budgeting, languages. Small is perfect.",
    category: "connection",
    kind: "skill-swap",
    timeframe: "One exchange",
    waypoints: ["solo-among-others", "independence-lab"],
  },
  {
    id: "imperfect-making",
    title: "Make something imperfect",
    description: "An hour of making that is not for praise.",
    prompt:
      "Spend an hour making something you will not show for approval. A page, a photo, a melody, a crooked sketch.",
    category: "creativity",
    kind: "practice",
    timeframe: "An hour",
    waypoints: ["emotional-clarity"],
  },
  {
    id: "museum-sentence",
    title: "Leave with one sentence",
    description: "Stay in a room until language arrives.",
    prompt:
      "Visit a museum, a gallery, or even a shop window with real attention. Stay until one sentence arrives. Write it down before you leave.",
    category: "creativity",
    kind: "practice",
    timeframe: "An outing",
    waypoints: ["solo-among-others", "emotional-clarity"],
  },
  {
    id: "orange-corner",
    title: "Change one corner",
    description: "The room does not have to wait for a different life.",
    prompt:
      "Paint, move, or dress one corner of home in a color you have been waiting to deserve. Orange is allowed.",
    category: "creativity",
    kind: "practice",
    timeframe: "A weekend hour",
    waypoints: ["independence-lab"],
  },
  {
    id: "morning-system",
    title: "One system that makes morning kinder",
    description: "Not a new personality. One kinder setup.",
    prompt:
      "Design a single tiny home system: the bag by the door, the pan that lives out, the light you turn on first. Use it for seven days.",
    category: "home",
    kind: "practice",
    timeframe: "One week",
    waypoints: ["independence-lab"],
  },
  {
    id: "meal-for-you",
    title: "Cook the meal you were saving",
    description: "Stop reserving the good meal for company.",
    prompt:
      "Make a meal you usually postpone until someone else is there. Set a place. Eat it while it is hot.",
    category: "home",
    kind: "practice",
    timeframe: "An evening",
    waypoints: ["independence-lab"],
  },
  {
    id: "room-for-this-life",
    title: "Arrange the room for the life you have",
    description: "Let the furniture tell the truth.",
    prompt:
      "Move one piece of furniture so the room fits how you actually live, not how you thought you would.",
    category: "home",
    kind: "practice",
    timeframe: "An afternoon",
    waypoints: ["independence-lab", "emotional-clarity"],
  },
  {
    id: "first-hour",
    title: "The first hour of a postponed skill",
    description: "You do not need a plan for the whole craft.",
    prompt:
      "Learn only the first hour of something you have been waiting to start properly. Stop when the hour ends.",
    category: "growth",
    kind: "practice",
    timeframe: "One hour",
    waypoints: ["emotional-clarity"],
  },
  {
    id: "unsent-letter",
    title: "Write the unsent letter",
    description: "A private conversation with the part of you that keeps waiting.",
    prompt:
      "Write to the version of you who keeps waiting. You do not have to send it. You do have to be specific.",
    category: "growth",
    kind: "practice",
    timeframe: "An evening",
    waypoints: ["emotional-clarity"],
  },
  {
    id: "one-witness",
    title: "One goal, one witness",
    description: "A goal with a person beside it, not a scoreboard.",
    prompt:
      "Name a goal that matters this month. Ask one person to witness it with you. Weekly, briefly, without advice unless you ask.",
    category: "growth",
    kind: "same",
    timeframe: "This month",
    waypoints: ["emotional-clarity", "independence-lab"],
  },
  {
    id: "table-for-one",
    title: "A table with good light",
    description: "A meal out that is the plan, not the waiting room.",
    prompt:
      "Eat one meal out, alone, somewhere with kind light. Stay for the whole meal. Order the thing you want.",
    category: "wellbeing",
    kind: "practice",
    timeframe: "One meal",
    waypoints: ["solo-among-others"],
  },
  {
    id: "walk-no-podcast",
    title: "A walk with no soundtrack",
    description: "Let the day be slightly ordinary.",
    prompt:
      "Walk for thirty minutes without headphones. Let the day be slightly boring. See what shows up.",
    category: "wellbeing",
    kind: "practice",
    timeframe: "Thirty minutes",
    waypoints: ["emotional-clarity"],
  },
  {
    id: "phone-in-another-room",
    title: "An evening in another room from your phone",
    description: "Give one evening back to your hands.",
    prompt:
      "Put your phone in another room for one evening. Do something with your hands. Notice the shape of the time.",
    category: "wellbeing",
    kind: "practice",
    timeframe: "One evening",
    waypoints: ["independence-lab", "emotional-clarity"],
  },
];

export function getSeed(id: string) {
  return SEEDS.find((seed) => seed.id === id);
}

export function getWaypoint(idOrSlug: string) {
  return WAYPOINTS.find((waypoint) => waypoint.id === idOrSlug || waypoint.slug === idOrSlug);
}

export const ADMIN_PREFIX = "/admin";

export function isAdminPath(pathname: string) {
  return pathname === ADMIN_PREFIX || pathname.startsWith(`${ADMIN_PREFIX}/`);
}

export const MEMBER_PREFIXES = [
  "/dashboard",
  "/onboarding",
  "/out-there",
  "/campfire",
  "/profile",
  "/settings",
  "/experiences",
  "/partnerships",
  "/first-night-kits",
];

export function isMemberPath(pathname: string) {
  return MEMBER_PREFIXES.some(
    (prefix) => pathname === prefix || pathname.startsWith(`${prefix}/`),
  );
}
