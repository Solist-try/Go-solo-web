"use client";

import { useState } from "react";
import { DeskIntro, TextField, deskButton } from "@/components/gosolo/admin/ui";
import { Button } from "@/components/ui/button";
import { note, type SiteContent } from "@/lib/admin/state";
import { useGoSolo } from "@/lib/gosolo";

export function ContentAdmin() {
  const { content, updateDesk } = useGoSolo();
  const [draft, setDraft] = useState<SiteContent>(content);
  const [saved, setSaved] = useState("");

  function patch(partial: Partial<SiteContent>) {
    setDraft({ ...draft, ...partial });
  }

  return (
    <>
      <DeskIntro eyebrow="Content" title="The words of the house.">
        Edit the public pages and the letters. Saving changes what this browser shows. With Supabase, the same words are stored for everyone.
      </DeskIntro>
      <form
        className="mt-8 max-w-2xl space-y-8"
        onSubmit={(event) => {
          event.preventDefault();
          updateDesk((admin) => note({ ...admin, content: draft }, "Updated the public words."));
          setSaved("Saved. The public home uses these words now.");
        }}
      >
        <section className="space-y-4">
          <h2 className="font-serif text-3xl">Homepage hero</h2>
          <TextField label="Tagline" value={draft.heroTagline} onChange={(heroTagline) => patch({ heroTagline })} />
          <TextField label="Eyebrow" value={draft.heroEyebrow} onChange={(heroEyebrow) => patch({ heroEyebrow })} />
          <TextField label="Headline" value={draft.heroTitle} onChange={(heroTitle) => patch({ heroTitle })} />
          <TextField label="Subhead" value={draft.heroSubhead} onChange={(heroSubhead) => patch({ heroSubhead })} />
          <TextField label="Supporting copy" value={draft.heroSupport} onChange={(heroSupport) => patch({ heroSupport })} area />
          <TextField label="Lede" value={draft.heroLede} onChange={(heroLede) => patch({ heroLede })} area />
          <TextField label="Primary button" value={draft.heroPrimary} onChange={(heroPrimary) => patch({ heroPrimary })} />
          <TextField label="Secondary button" value={draft.heroSecondary} onChange={(heroSecondary) => patch({ heroSecondary })} />
          <TextField
            label="Phrase cards, one per line"
            value={draft.phrases.join("\n")}
            onChange={(value) => patch({ phrases: value.split("\n").map((line) => line.trim()).filter(Boolean) })}
            area
          />
        </section>
        <section className="space-y-4">
          <h2 className="font-serif text-3xl">Homepage philosophy</h2>
          <TextField label="Headline" value={draft.philosophyTitle} onChange={(philosophyTitle) => patch({ philosophyTitle })} />
          <TextField
            label="Paragraphs, separated by a blank line"
            value={draft.philosophyParagraphs.join("\n\n")}
            onChange={(value) => patch({ philosophyParagraphs: value.split(/\n\s*\n/).map((line) => line.trim()).filter(Boolean) })}
            area
          />
          <TextField label="Closing statement" value={draft.philosophyClose} onChange={(philosophyClose) => patch({ philosophyClose })} />
          <TextField label="Belief" value={draft.belief} onChange={(belief) => patch({ belief })} />
          <TextField label="Belief, second line" value={draft.beliefSecond} onChange={(beliefSecond) => patch({ beliefSecond })} />
        </section>
        <section className="space-y-4">
          <h2 className="font-serif text-3xl">How it works</h2>
          <TextField label="Section title" value={draft.howTitle} onChange={(howTitle) => patch({ howTitle })} />
          {draft.steps.map((step, index) => (
            <div key={`${step.mark}-${index}`} className="grid gap-3 rounded-[24px] bg-white/70 p-4 sm:grid-cols-[80px_1fr]">
              <TextField label="Mark" value={step.mark} onChange={(mark) => {
                const steps = draft.steps.slice();
                steps[index] = { ...step, mark };
                patch({ steps });
              }} />
              <div className="space-y-3">
                <TextField label="Name" value={step.name} onChange={(name) => {
                  const steps = draft.steps.slice();
                  steps[index] = { ...step, name };
                  patch({ steps });
                }} />
                <TextField label="Line" value={step.body} onChange={(body) => {
                  const steps = draft.steps.slice();
                  steps[index] = { ...step, body };
                  patch({ steps });
                }} />
              </div>
            </div>
          ))}
        </section>
        <section className="space-y-4">
          <h2 className="font-serif text-3xl">Footer manifesto</h2>
          <TextField
            label="Lines, separated by a blank line"
            value={draft.manifesto.join("\n\n")}
            onChange={(value) => patch({ manifesto: value.split(/\n\s*\n/).map((line) => line.trim()).filter(Boolean) })}
            area
          />
        </section>
        <section className="space-y-4">
          <h2 className="font-serif text-3xl">Coming soon cards</h2>
          <TextField label="Intro" value={draft.comingSoonIntro} onChange={(comingSoonIntro) => patch({ comingSoonIntro })} area />
          {draft.comingSoon.map((card, index) => (
            <div key={`${card.title}-${index}`} className="space-y-3 rounded-[24px] bg-white/70 p-4">
              <TextField label="Title" value={card.title} onChange={(title) => {
                const comingSoon = draft.comingSoon.slice();
                comingSoon[index] = { ...card, title };
                patch({ comingSoon });
              }} />
              <TextField label="Body" value={card.body} onChange={(body) => {
                const comingSoon = draft.comingSoon.slice();
                comingSoon[index] = { ...card, body };
                patch({ comingSoon });
              }} area />
            </div>
          ))}
        </section>
        <section className="space-y-4">
          <h2 className="font-serif text-3xl">Emails</h2>
          <p className="text-ink-soft">These are the letters Go Solo can send. Editing them changes the words.</p>
          {draft.emails.map((email, index) => (
            <div key={email.id} className="space-y-3 rounded-[24px] bg-gold/60 p-4">
              <p className="text-sm text-ink-soft">{email.name}</p>
              <TextField label="Subject" value={email.subject} onChange={(subject) => {
                const emails = draft.emails.slice();
                emails[index] = { ...email, subject };
                patch({ emails });
              }} />
              <TextField label="Body" value={email.body} onChange={(body) => {
                const emails = draft.emails.slice();
                emails[index] = { ...email, body };
                patch({ emails });
              }} area />
            </div>
          ))}
        </section>
        <Button type="submit" className={deskButton}>Save words</Button>
        {saved ? <p className="text-sm text-ink-soft">{saved}</p> : null}
      </form>
    </>
  );
}
