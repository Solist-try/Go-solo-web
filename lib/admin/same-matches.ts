import { supportLabel } from "@/lib/garden";
import { INTERESTS, type Partnership, type Profile, type Seed } from "@/lib/types";

export type SameSuggestion = {
  key: string;
  seedId: string;
  seedTitle: string;
  leftId: string;
  rightId: string;
  leftGoal: string;
  rightGoal: string;
  reasons: string[];
};

export type OpenSameChair = {
  id: string;
  seedId: string;
  seedTitle: string;
  userId: string;
  goal: string;
};

export function samePairKey(seedId: string, a: string, b: string) {
  const [first, second] = [a, b].sort();
  return `${seedId}:${first}:${second}`;
}

function shared(left?: string[], right?: string[]) {
  const other = new Set((right ?? []).map((item) => item.toLowerCase()));
  return (left ?? []).filter((item) => other.has(item.toLowerCase()));
}

function interestLabel(id: string) {
  return INTERESTS.find((item) => item.id === id)?.label ?? id;
}

function openSeekers(partnerships: Partnership[]) {
  const matched = new Set<string>();
  for (const item of partnerships) {
    if (item.status !== "matched") continue;
    matched.add(`${item.seedId}:${item.seekerId}`);
    if (item.partnerId) matched.add(`${item.seedId}:${item.partnerId}`);
  }
  return partnerships.filter(
    (item) => item.status === "seeking" && !item.partnerId && !matched.has(`${item.seedId}:${item.seekerId}`),
  );
}

export function suggestSameMatches(input: {
  partnerships: Partnership[];
  profiles: Profile[];
  seeds: Seed[];
  dismissed?: string[];
}): SameSuggestion[] {
  const dismissed = new Set(input.dismissed ?? []);
  const names = new Map(input.profiles.map((profile) => [profile.id, profile]));
  const seedById = new Map(input.seeds.map((seed) => [seed.id, seed]));
  const bySeed = new Map<string, Partnership[]>();
  for (const item of openSeekers(input.partnerships)) {
    const seed = seedById.get(item.seedId);
    if (seed && seed.kind !== "same") continue;
    bySeed.set(item.seedId, [...(bySeed.get(item.seedId) ?? []), item]);
  }

  const suggestions: SameSuggestion[] = [];
  for (const [seedId, people] of bySeed) {
    const seed = seedById.get(seedId);
    for (let i = 0; i < people.length; i += 1) {
      for (let j = i + 1; j < people.length; j += 1) {
        const left = people[i];
        const right = people[j];
        if (left.seekerId === right.seekerId) continue;
        const key = samePairKey(seedId, left.seekerId, right.seekerId);
        if (dismissed.has(key)) continue;
        const a = names.get(left.seekerId);
        const b = names.get(right.seekerId);
        const reasons = [`Both are open on ${seed?.title ?? "this seed"}.`];
        const support = shared(a?.supportWith, b?.supportWith).map(supportLabel).filter(Boolean);
        if (support.length > 0) reasons.push(`Both asked for ${support.join(", ")}.`);
        const interests = shared(a?.interests, b?.interests).map(interestLabel);
        if (interests.length > 0) reasons.push(`Shared interests: ${interests.join(", ")}.`);
        suggestions.push({
          key,
          seedId,
          seedTitle: seed?.title ?? "A seed",
          leftId: left.seekerId,
          rightId: right.seekerId,
          leftGoal: left.goal,
          rightGoal: right.goal,
          reasons,
        });
      }
    }
  }

  return suggestions.sort((a, b) => b.reasons.length - a.reasons.length || a.seedTitle.localeCompare(b.seedTitle));
}

export function openSameChairs(input: {
  partnerships: Partnership[];
  seeds: Seed[];
}): OpenSameChair[] {
  const seedById = new Map(input.seeds.map((seed) => [seed.id, seed]));
  const bySeed = new Map<string, Partnership[]>();
  for (const item of openSeekers(input.partnerships)) {
    const seed = seedById.get(item.seedId);
    if (seed && seed.kind !== "same") continue;
    bySeed.set(item.seedId, [...(bySeed.get(item.seedId) ?? []), item]);
  }
  const waiting: OpenSameChair[] = [];
  for (const [seedId, people] of bySeed) {
    if (people.length !== 1) continue;
    const person = people[0];
    waiting.push({
      id: person.id,
      seedId,
      seedTitle: seedById.get(seedId)?.title ?? "A seed",
      userId: person.seekerId,
      goal: person.goal,
    });
  }
  return waiting;
}
