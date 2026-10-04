import {
  CAMPFIRE_KINDS,
  CAMPFIRE_SECTIONS,
  INTERESTS,
  REACTIONS,
  SEED_CATEGORIES,
  WOULD_AGAIN,
  type Reaction,
  type ReactionKind,
} from "@/lib/types";

export function labelFrom<T extends { id: string; label: string }>(
  list: readonly T[],
  id?: string,
) {
  return list.find((item) => item.id === id)?.label ?? "";
}

export function categoryLabel(id?: string) {
  return labelFrom(SEED_CATEGORIES, id);
}

export function interestLabel(id: string) {
  return labelFrom(INTERESTS, id);
}

export function sectionLabel(id?: string) {
  return labelFrom(CAMPFIRE_SECTIONS, id);
}

export function kindLabel(id?: string) {
  return labelFrom(CAMPFIRE_KINDS, id);
}

export function againLabel(id?: string) {
  return labelFrom(WOULD_AGAIN, id);
}

export function formatDate(iso: string) {
  const date = new Date(iso);
  if (Number.isNaN(date.getTime())) return "";
  return new Intl.DateTimeFormat("en-GB", {
    day: "numeric",
    month: "long",
    year: "numeric",
    timeZone: "UTC",
  }).format(date);
}

export function formatMonthYear(iso: string) {
  const date = new Date(iso);
  if (Number.isNaN(date.getTime())) return "";
  return new Intl.DateTimeFormat("en-GB", {
    month: "long",
    year: "numeric",
    timeZone: "UTC",
  }).format(date);
}

export function formatRelative(iso: string, now = new Date()) {
  const date = new Date(iso);
  if (Number.isNaN(date.getTime())) return "";
  const startNow = Date.UTC(now.getUTCFullYear(), now.getUTCMonth(), now.getUTCDate());
  const startThen = Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate());
  const days = Math.round((startNow - startThen) / 86_400_000);
  if (days <= 0) return "today";
  if (days === 1) return "yesterday";
  if (days < 14) return `${days} days ago`;
  return formatDate(iso);
}

export function reactionPhrase(kind: ReactionKind) {
  return REACTIONS.find((item) => item.id === kind)?.phrase ?? "";
}

export function describeReactions(
  reactions: Reaction[],
  names: Map<string, string>,
  kind: ReactionKind,
) {
  const people = reactions
    .filter((reaction) => reaction.kind === kind)
    .map((reaction) => names.get(reaction.userId))
    .filter((name): name is string => Boolean(name));
  if (people.length === 0) return null;
  const phrase = reactionPhrase(kind);
  if (people.length === 1) return `${people[0]} ${phrase}`;
  if (people.length === 2) return `${people[0]} and ${people[1]} ${phrase}`;
  return `${people[0]}, ${people[1]}, and others ${phrase}`;
}

export function safeNext(value: string | null | undefined, fallback = "/dashboard") {
  if (!value || !value.startsWith("/") || value.startsWith("//") || value.includes("\\")) {
    return fallback;
  }
  return value;
}

export function sortByNewest<T extends { createdAt: string }>(items: T[]) {
  return [...items].sort((a, b) => (a.createdAt < b.createdAt ? 1 : -1));
}

export function initials(name: string) {
  const parts = name.trim().split(/\s+/).slice(0, 2);
  if (parts.length === 0 || !parts[0]) return "G";
  return parts.map((part) => part[0]?.toUpperCase() ?? "").join("");
}
