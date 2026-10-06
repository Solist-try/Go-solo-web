"use client";

import { useState } from "react";
import { Choice, DeskIntro, deskButton } from "@/components/gosolo/admin/ui";
import { openSameChairs, suggestSameMatches } from "@/lib/admin/same-matches";
import { note } from "@/lib/admin/state";
import { useGoSolo } from "@/lib/gosolo";
import { Button } from "@/components/ui/button";

export function SameMatchesAdmin() {
  const { library, seeds, desk, updateDesk, confirmSameMatch } = useGoSolo();
  const [seedId, setSeedId] = useState("");
  const [leftId, setLeftId] = useState("");
  const [rightId, setRightId] = useState("");
  const [error, setError] = useState("");
  const [saved, setSaved] = useState("");

  const names = new Map(library.profiles.map((profile) => [profile.id, profile]));
  const suggestions = suggestSameMatches({
    partnerships: library.partnerships,
    profiles: library.profiles,
    seeds,
    dismissed: desk.dismissedSameMatches,
  });
  const waiting = openSameChairs({ partnerships: library.partnerships, seeds });
  const sameSeeds = seeds.filter((seed) => seed.kind === "same");
  const members = library.profiles.filter(
    (profile) => profile.role !== "admin" && profile.status !== "suspended" && profile.status !== "deactivated",
  );

  async function makeMatch(nextSeedId: string, a: string, b: string) {
    setError("");
    setSaved("");
    const result = await confirmSameMatch(nextSeedId, a, b);
    if (result.error) {
      setError(result.error);
      return;
    }
    const left = names.get(a)?.displayName || "Someone";
    const right = names.get(b)?.displayName || "someone";
    updateDesk((admin) => note(admin, `Made a SAME match for ${left} and ${right}.`));
    setSaved(`${left} and ${right} are matched. They can take their time with it.`);
  }

  return (
    <>
      <DeskIntro eyebrow="SAME" title="Suggested SAME Matches">
        A suggestion appears when two people are open on the same seed. Nothing joins them until you make the match.
      </DeskIntro>

      {suggestions.length === 0 ? (
        <p className="mt-8 max-w-xl text-lg text-ink-soft">No suggested matches yet.</p>
      ) : (
        <ul className="mt-10 space-y-4">
          {suggestions.map((item) => {
            const left = names.get(item.leftId);
            const right = names.get(item.rightId);
            return (
              <li key={item.key} className="rounded-[28px] bg-sage p-6 sm:p-8">
                <p className="text-sm text-ink-soft">{item.seedTitle}</p>
                <h2 className="mt-2 font-serif text-3xl tracking-tight text-ink">
                  {left?.displayName || "Someone"} and {right?.displayName || "someone"}
                </h2>
                <div className="mt-4 grid gap-4 text-ink sm:grid-cols-2">
                  <p>{item.leftGoal}</p>
                  <p>{item.rightGoal}</p>
                </div>
                <ul className="mt-4 space-y-1 text-sm text-ink-soft">
                  {item.reasons.map((reason) => (
                    <li key={reason}>{reason}</li>
                  ))}
                </ul>
                <div className="mt-6 flex flex-wrap gap-3">
                  <Button className={deskButton} onClick={() => void makeMatch(item.seedId, item.leftId, item.rightId)}>
                    Make this match
                  </Button>
                  <Button
                    variant="outline"
                    className={`${deskButton} bg-transparent`}
                    onClick={() =>
                      updateDesk((admin) =>
                        note(
                          {
                            ...admin,
                            dismissedSameMatches: [...admin.dismissedSameMatches, item.key],
                          },
                          "Set aside a SAME suggestion.",
                        ),
                      )
                    }
                  >
                    Not this pair
                  </Button>
                </div>
              </li>
            );
          })}
        </ul>
      )}

      <section className="mt-14 max-w-xl space-y-4">
        <h2 className="font-serif text-3xl tracking-tight text-ink">Choose two people</h2>
        <p className="text-ink-soft">Use this when you already know the fit. It still waits for you.</p>
        <Choice
          label="Seed"
          value={seedId}
          options={[{ id: "", label: "Choose a seed" }, ...sameSeeds.map((seed) => ({ id: seed.id, label: seed.title }))]}
          onChange={setSeedId}
        />
        <Choice
          label="First person"
          value={leftId}
          options={[{ id: "", label: "Choose a person" }, ...members.map((profile) => ({ id: profile.id, label: profile.displayName || profile.email }))]}
          onChange={setLeftId}
        />
        <Choice
          label="Second person"
          value={rightId}
          options={[
            { id: "", label: "Choose a person" },
            ...members
              .filter((profile) => profile.id !== leftId)
              .map((profile) => ({ id: profile.id, label: profile.displayName || profile.email })),
          ]}
          onChange={setRightId}
        />
        <Button
          className={deskButton}
          onClick={() => {
            if (!seedId || !leftId || !rightId) {
              setError("Choose a seed and two people.");
              return;
            }
            void makeMatch(seedId, leftId, rightId);
          }}
        >
          Make this match
        </Button>
        {error ? <p className="text-sm text-destructive">{error}</p> : null}
        {saved ? <p className="text-ink">{saved}</p> : null}
      </section>

      {waiting.length > 0 ? (
        <section className="mt-14">
          <h2 className="font-serif text-3xl tracking-tight text-ink">Waiting for someone else</h2>
          <ul className="mt-4 space-y-3">
            {waiting.map((item) => (
              <li key={item.id} className="text-ink">
                <span className="text-ink-soft">{item.seedTitle}</span>
                <span className="mt-1 block text-lg">{names.get(item.userId)?.displayName || "Someone"}</span>
                <span className="mt-1 block text-ink-soft">{item.goal}</span>
              </li>
            ))}
          </ul>
        </section>
      ) : null}
    </>
  );
}
