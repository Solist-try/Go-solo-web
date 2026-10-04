"use client";

import Link from "next/link";
import { Frame, Panel } from "@/components/gosolo/pieces";
import { useGoSolo } from "@/lib/gosolo";

const BELIEFS = [
  {
    title: "Independence And Connection Can Coexist",
    body: "A life of your own can still have people in it. One does not cancel the other.",
    tone: "bg-sage",
  },
  {
    title: "Small Actions Matter",
    body: "A walk, a class, a meal. A beginning does not need to be large to be real.",
    tone: "bg-clay",
  },
  {
    title: "Life Happens Offline",
    body: "The interesting part is rarely the screen. Go out, then come back and tell the truth about it.",
    tone: "bg-gold",
  },
  {
    title: "Community Should Support Life, Not Replace It",
    body: "This is a chair you can leave. The room is here so the rest of your life can get bigger.",
    tone: "bg-mist",
  },
  {
    title: "You Do Not Need Permission To Begin",
    body: "Waiting for the right company is a habit. It is not a rule.",
    tone: "bg-sage",
  },
] as const;

const MISSION = [
  "Living alone is often framed as a limitation.",
  "But independence can create possibilities too.",
  "You can travel.",
  "Learn.",
  "Explore.",
  "Experiment.",
  "Build a life that reflects who you are.",
  "Go Solo helps people do that while staying connected to others.",
];

export function AboutPage() {
  const { content } = useGoSolo();
  const name = content.founderName.trim();
  const photo = content.founderPhoto.trim() || "/founder-chair.svg";
  const letter = content.founderLetter.filter((paragraph) => paragraph.trim());

  return (
    <Frame>
      <header className="max-w-3xl">
        <h1 className="font-serif text-5xl leading-[1.05] tracking-tight text-ink sm:text-7xl">
          Why Go Solo Exists
        </h1>
        <div className="mt-8 space-y-4 text-xl leading-relaxed text-ink-soft sm:text-2xl">
          <p>
            Many people postpone parts of their lives while waiting for the right person, the right
            timing, or the right circumstances.
          </p>
          <p>Go Solo explores what happens when we stop waiting.</p>
        </div>
      </header>

      <section className="mt-20 max-w-3xl" aria-labelledby="mission-title">
        <h2 id="mission-title" className="font-serif text-4xl tracking-tight text-ink sm:text-5xl">
          Life Doesn&apos;t Need To Be On Hold
        </h2>
        <div className="mt-8 space-y-4 text-lg leading-relaxed text-ink sm:text-xl">
          {MISSION.map((line) => (
            <p key={line}>{line}</p>
          ))}
        </div>
      </section>

      <section className="mt-20" aria-labelledby="beliefs-title">
        <h2 id="beliefs-title" className="font-serif text-4xl tracking-tight text-ink sm:text-5xl">
          What We Believe
        </h2>
        <ul className="mt-8 grid gap-4 sm:grid-cols-2">
          {BELIEFS.map((belief) => (
            <li key={belief.title} className={`rounded-[28px] p-7 ${belief.tone}`}>
              <h3 className="font-serif text-3xl leading-snug tracking-tight text-ink">{belief.title}</h3>
              <p className="mt-4 text-lg leading-relaxed text-ink">{belief.body}</p>
            </li>
          ))}
        </ul>
      </section>

      <section className="mt-20" aria-labelledby="founder-title">
        <h2 id="founder-title" className="font-serif text-4xl tracking-tight text-ink sm:text-5xl">
          Meet The Founder
        </h2>
        <div className="mt-8 grid items-start gap-8 lg:grid-cols-[minmax(0,280px)_1fr]">
          <figure>
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img
              src={photo}
              alt={name ? `Photograph of ${name}` : "The person who tends Go Solo, sitting with a cup"}
              className="aspect-[4/5] w-full rounded-[28px] object-cover"
            />
          </figure>
          <div className="max-w-2xl">
            {name ? <p className="font-serif text-4xl tracking-tight text-ink">{name}</p> : null}
            <p className="mt-4 text-lg leading-relaxed text-ink">{content.founderBio}</p>
            <div className="mt-6 space-y-4 text-lg leading-relaxed text-ink-soft">
              {letter.map((paragraph) => (
                <p key={paragraph}>{paragraph}</p>
              ))}
            </div>
            <div className="mt-8">
              <h3 className="text-sm text-ink-soft">The best ways to reach me</h3>
              <ul className="mt-3 space-y-2 text-lg text-ink">
                <li>
                  <Link href="/contact" className="underline decoration-ink/20 underline-offset-4">
                    Write a note
                  </Link>
                  . It comes to me, and I read it.
                </li>
                {content.founderEmail.trim() ? (
                  <li>
                    <a
                      href={`mailto:${content.founderEmail.trim()}`}
                      className="underline decoration-ink/20 underline-offset-4"
                    >
                      {content.founderEmail.trim()}
                    </a>
                  </li>
                ) : (
                  <li>If you would rather write by email, leave your address in the note and I will answer there.</li>
                )}
              </ul>
            </div>
          </div>
        </div>
      </section>

      <Panel tone="gold" className="mt-20">
        <h2 className="font-serif text-4xl tracking-tight text-ink sm:text-5xl">Pull Up A Chair</h2>
        <div className="mt-6 max-w-2xl space-y-4 text-lg leading-relaxed text-ink sm:text-xl">
          <p>Whether you&apos;re living alone by choice, circumstance, or transition, you are welcome here.</p>
          <p>Go Solo is a place for people building meaningful lives on their own terms.</p>
        </div>
        <p className="mt-8">
          <Link href="/register" className="text-lg underline decoration-ink/20 underline-offset-4">
            Join, when you are ready
          </Link>
        </p>
      </Panel>
    </Frame>
  );
}
