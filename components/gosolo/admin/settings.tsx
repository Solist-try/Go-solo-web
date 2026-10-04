"use client";

import { useState } from "react";
import { DeskIntro, TextField, deskButton, downloadJson } from "@/components/gosolo/admin/ui";
import { Button } from "@/components/ui/button";
import { note } from "@/lib/admin/state";
import { buildInsights } from "@/lib/admin/insights";
import { useGoSolo } from "@/lib/gosolo";

export function SettingsAdmin() {
  const { desk, updateDesk, user, library, seeds, waypoints } = useGoSolo();
  const [noteText, setNoteText] = useState(desk.settings.note);
  const [warnTemplate, setWarnTemplate] = useState(desk.settings.warnTemplate);
  const [saved, setSaved] = useState("");

  return (
    <>
      <DeskIntro eyebrow="Settings" title="How the desk behaves.">
        A short reminder for stewards, the words used when someone needs a gentler version, and a way to take the reading with you.
      </DeskIntro>
      <form
        className="mt-8 max-w-2xl space-y-5"
        onSubmit={(event) => {
          event.preventDefault();
          updateDesk((admin) => note({ ...admin, settings: { note: noteText, warnTemplate } }, "Updated desk settings."));
          setSaved("Saved.");
        }}
      >
        <TextField label="Steward note" value={noteText} onChange={setNoteText} area />
        <TextField label="Warning message" value={warnTemplate} onChange={setWarnTemplate} area />
        <Button type="submit" className={deskButton}>Save settings</Button>
        {saved ? <p className="text-sm text-ink-soft">{saved}</p> : null}
      </form>
      <section className="mt-12 max-w-2xl space-y-3">
        <h2 className="font-serif text-3xl">This steward</h2>
        <p>{user?.displayName}</p>
        <p className="text-ink-soft">{user?.email}</p>
        <Button
          type="button"
          variant="outline"
          className={`${deskButton} bg-transparent`}
          onClick={() =>
            downloadJson("gosolo-desk.json", {
              content: desk.content,
              reports: desk.reports,
              journal: desk.journal,
              settings: desk.settings,
              insight: buildInsights({ world: library, seeds, waypoints }),
            })
          }
        >
          Export data
        </Button>
      </section>
      <section className="mt-12 max-w-2xl">
        <h2 className="font-serif text-3xl">Recent care</h2>
        <ul className="mt-4 space-y-2">
          {desk.journal.length === 0 ? <li className="text-ink-soft">No changes yet.</li> : null}
          {desk.journal.map((entry) => (
            <li key={entry.id}>{entry.text}</li>
          ))}
        </ul>
      </section>
    </>
  );
}
