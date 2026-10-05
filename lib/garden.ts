import { CHECK_IN_FREQUENCIES, CHECK_IN_STYLES, SUPPORT_WITH, type Profile } from "@/lib/types";

export function gardenOf(profile: Profile) {
  return {
    growing: profile.growing ?? [],
    helpGrowing: profile.helpGrowing ?? [],
    helpPlant: profile.helpPlant ?? [],
    helpPlantNote: profile.helpPlantNote ?? "",
    supportWith: profile.supportWith ?? [],
    checkInFrequency: profile.checkInFrequency ?? "",
    checkInStyle: profile.checkInStyle ?? "",
    sameNotes: profile.sameNotes ?? "",
  };
}

export function labelOf(list: readonly { id: string; label: string }[], id?: string) {
  if (!id) return "";
  return list.find((item) => item.id === id)?.label ?? "";
}

export function supportLabel(id: string) {
  return labelOf(SUPPORT_WITH, id) || id;
}

export function frequencyLabel(id?: string) {
  return labelOf(CHECK_IN_FREQUENCIES, id);
}

export function styleLabel(id?: string) {
  return labelOf(CHECK_IN_STYLES, id);
}

export function uniqueNames(items: string[]) {
  const seen = new Set<string>();
  const names: string[] = [];
  for (const item of items) {
    const name = item.trim();
    const key = name.toLowerCase();
    if (!key || seen.has(key)) continue;
    seen.add(key);
    names.push(name);
  }
  return names;
}
