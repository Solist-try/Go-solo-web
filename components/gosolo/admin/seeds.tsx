"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useState } from "react";
import { Choice, DeskIntro, TextField, deskButton } from "@/components/gosolo/admin/ui";
import { Button } from "@/components/ui/button";
import { SEEDS } from "@/lib/catalog";
import { resolveSeeds, newSeedId } from "@/lib/admin/resolve";
import { DIFFICULTIES, note } from "@/lib/admin/state";
import { categoryLabel } from "@/lib/format";
import { useGoSolo } from "@/lib/gosolo";
import { SEED_CATEGORIES, type Seed, type SeedCategory, type SeedDifficulty, type SeedKind } from "@/lib/types";

const kinds: { id: SeedKind; label: string }[] = [
  { id: "practice", label: "Practice" },
  { id: "same", label: "SAME" },
  { id: "skill-swap", label: "Skill swap" },
];

function blankSeed(): Seed {
  return {
    id: "",
    title: "",
    description: "",
    prompt: "",
    category: "adventure",
    kind: "practice",
    timeframe: "An afternoon",
    waypoints: [],
    difficulty: "gentle",
  };
}

export function SeedsAdmin() {
  const { desk, library } = useGoSolo();
  const seeds = resolveSeeds(desk, true);
  return (
    <>
      <DeskIntro eyebrow="Seeds" title="Possibilities, tended.">
        Create, edit, feature, or rest a seed. Starts and completions stay beside the words.
      </DeskIntro>
      <Button asChild className={`${deskButton} mt-8`}>
        <Link href="/admin/seeds/new">Create seed</Link>
      </Button>
      <ul className="mt-8 divide-y divide-ink/10">
        {seeds.map((seed) => {
          const starts = library.userSeeds.filter((item) => item.seedId === seed.id);
          const done = starts.filter((item) => item.status === "completed").length;
          const stories = library.stories.filter((story) => story.seedId === seed.id).length;
          const archived = desk.archivedSeedIds.includes(seed.id);
          const featured = desk.featuredSeedIds.includes(seed.id);
          const rate = starts.length ? Math.round((done / starts.length) * 100) : 0;
          return (
            <li key={seed.id} className="flex flex-col gap-2 py-5 sm:flex-row sm:items-baseline sm:justify-between">
              <div>
                <Link href={`/admin/seeds/${seed.id}`} className="font-serif text-2xl text-ink">
                  {seed.title}
                </Link>
                <p className="mt-1 text-sm text-ink-soft">
                  {categoryLabel(seed.category)}
                  {featured ? " · Featured" : ""}
                  {archived ? " · Archived" : ""}
                </p>
              </div>
              <p className="text-sm text-ink-soft">
                {starts.length} starts · {done} completions · {rate}% · {stories} stories
              </p>
            </li>
          );
        })}
      </ul>
    </>
  );
}

export function SeedEditor({ id }: { id?: string }) {
  const { desk, library, updateDesk, waypoints } = useGoSolo();
  const router = useRouter();
  const existing = id ? resolveSeeds(desk, true).find((seed) => seed.id === id) : undefined;
  const [seed, setSeed] = useState<Seed>(existing ?? blankSeed());
  const [saved, setSaved] = useState("");
  const stories = library.stories.filter((story) => story.seedId === seed.id);
  const starts = library.userSeeds.filter((item) => item.seedId === seed.id);
  const done = starts.filter((item) => item.status === "completed").length;

  function persist(next: Seed) {
    updateDesk((admin) => {
      const catalog = SEEDS.some((item) => item.id === next.id);
      const custom = admin.customSeeds.some((item) => item.id === next.id);
      const state = catalog
        ? { ...admin, seedEdits: { ...admin.seedEdits, [next.id]: next } }
        : {
            ...admin,
            customSeeds: custom
              ? admin.customSeeds.map((item) => (item.id === next.id ? next : item))
              : [...admin.customSeeds, next],
          };
      return note(state, `Saved “${next.title || "Untitled seed"}”.`);
    });
    setSaved("Saved.");
  }

  function toggle(list: "archivedSeedIds" | "featuredSeedIds") {
    if (!seed.id) return;
    updateDesk((admin) => {
      const has = admin[list].includes(seed.id);
      const next = has ? admin[list].filter((item) => item !== seed.id) : [...admin[list], seed.id];
      const label = list === "archivedSeedIds" ? (has ? "Restored" : "Archived") : has ? "Unfeatured" : "Featured";
      return note({ ...admin, [list]: next }, `${label} “${seed.title}”.`);
    });
  }

  return (
    <>
      <DeskIntro eyebrow="Seed" title={seed.title || "A new seed"}>
        {seed.id && starts.length > 0
          ? `${starts.length} starts, ${done} completions, ${starts.length ? Math.round((done / starts.length) * 100) : 0}% completion, ${stories.length} related Out There posts.`
          : "A small possibility. Not a goal."}
      </DeskIntro>
      <form
        className="mt-8 max-w-2xl space-y-5"
        onSubmit={(event) => {
          event.preventDefault();
          const withId = { ...seed, id: seed.id || newSeedId() };
          setSeed(withId);
          persist(withId);
          if (!seed.id) router.replace(`/admin/seeds/${withId.id}`);
        }}
      >
        <TextField label="Title" value={seed.title} onChange={(title) => setSeed({ ...seed, title })} />
        <TextField label="Description" value={seed.description} onChange={(description) => setSeed({ ...seed, description })} area />
        <TextField label="The invitation" value={seed.prompt} onChange={(prompt) => setSeed({ ...seed, prompt })} area />
        <div className="grid gap-4 sm:grid-cols-2">
          <Choice
            label="Category"
            value={seed.category}
            options={SEED_CATEGORIES.map((item) => ({ id: item.id, label: item.label }))}
            onChange={(category) => setSeed({ ...seed, category: category as SeedCategory })}
          />
          <Choice
            label="Difficulty"
            value={seed.difficulty ?? "gentle"}
            options={DIFFICULTIES}
            onChange={(difficulty) => setSeed({ ...seed, difficulty: difficulty as SeedDifficulty })}
          />
          <TextField label="Estimated time" value={seed.timeframe} onChange={(timeframe) => setSeed({ ...seed, timeframe })} />
          <Choice label="Kind" value={seed.kind} options={kinds} onChange={(kind) => setSeed({ ...seed, kind: kind as SeedKind })} />
        </div>
        <fieldset>
          <legend className="text-sm">Related waypoints</legend>
          <div className="mt-2 flex flex-wrap gap-2">
            {waypoints.map((waypoint) => {
              const on = seed.waypoints.includes(waypoint.id);
              return (
                <button
                  key={waypoint.id}
                  type="button"
                  aria-pressed={on}
                  className={`rounded-full px-4 py-2 text-sm ${on ? "bg-ink text-background" : "bg-white text-ink"}`}
                  onClick={() =>
                    setSeed({
                      ...seed,
                      waypoints: on ? seed.waypoints.filter((item) => item !== waypoint.id) : [...seed.waypoints, waypoint.id],
                    })
                  }
                >
                  {waypoint.name}
                </button>
              );
            })}
          </div>
        </fieldset>
        <p className="text-sm text-ink">
          Featured: {seed.id && desk.featuredSeedIds.includes(seed.id) ? "yes" : "no"}
        </p>
        <div className="flex flex-wrap gap-2">
          <Button type="submit" className={deskButton}>
            Save seed
          </Button>
          {seed.id ? (
            <>
              <Button type="button" variant="outline" className={`${deskButton} bg-transparent`} onClick={() => toggle("featuredSeedIds")}>
                {desk.featuredSeedIds.includes(seed.id) ? "Unfeature" : "Feature"}
              </Button>
              <Button type="button" variant="outline" className={`${deskButton} bg-transparent`} onClick={() => toggle("archivedSeedIds")}>
                {desk.archivedSeedIds.includes(seed.id) ? "Restore" : "Archive"}
              </Button>
              <Button
                type="button"
                variant="outline"
                className={`${deskButton} bg-transparent`}
                onClick={() => {
                  const copy = { ...seed, id: newSeedId(), title: `${seed.title} copy` };
                  updateDesk((admin) => note({ ...admin, customSeeds: [...admin.customSeeds, copy] }, `Duplicated “${seed.title}”.`));
                  router.push(`/admin/seeds/${copy.id}`);
                }}
              >
                Duplicate
              </Button>
            </>
          ) : null}
        </div>
        {saved ? <p className="text-sm text-ink-soft">{saved}</p> : null}
      </form>
      <section className="mt-10">
        <h2 className="font-serif text-3xl">Related Out There stories</h2>
        <ul className="mt-4 space-y-2">
          {stories.length === 0 ? <li className="text-ink-soft">None linked yet.</li> : null}
          {stories.map((story) => (
            <li key={story.id}>
              <Link href={`/admin/out-there`} className="underline decoration-ink/15 underline-offset-4">
                {story.title}
              </Link>
            </li>
          ))}
        </ul>
      </section>
    </>
  );
}
