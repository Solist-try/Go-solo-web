"use client";

import { Frame, Panel } from "@/components/gosolo/pieces";
import { useGoSolo } from "@/lib/gosolo";

const WAITING = [
  "The right person",
  "The right timing",
  "The right schedule",
  "More confidence",
  "Better circumstances",
];

export function AboutPage() {
  const { content } = useGoSolo();
  const photo = content.founderPhoto.trim();

  return (
    <Frame>
      <header className="max-w-3xl">
        <p className="text-sm text-ink-soft">About Go Solo</p>
        <h1 className="mt-4 font-serif text-5xl leading-[1.05] tracking-tight text-ink sm:text-7xl">
          Why Go Solo Exists
        </h1>
        <div className="mt-8 max-w-2xl space-y-4 text-xl leading-relaxed text-ink sm:text-2xl">
          <p>Go Solo began with a simple observation.</p>
          <p>Many parts of life are built around couples, families and established social circles.</p>
          <p>But more and more people are navigating the world independently.</p>
          <p>Sometimes by choice. Sometimes by circumstance.</p>
          <p>Yet the world has not always caught up.</p>
        </div>
      </header>

      <section className="mt-20" aria-labelledby="founder-title">
        <div className="grid items-start gap-10 lg:grid-cols-[minmax(0,280px)_1fr]">
          <figure>
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img
              src={photo || "/founder-chair.svg"}
              alt={photo ? "Marge Aliaga" : "A quiet drawing of someone sitting with a cup"}
              className="aspect-[4/5] w-full rounded-[28px] object-cover"
            />
          </figure>
          <div className="max-w-2xl">
            <h2 id="founder-title" className="font-serif text-4xl tracking-tight text-ink sm:text-6xl">
              Hi, I&apos;m Marge.
            </h2>
            <div className="mt-8 space-y-4 text-lg leading-relaxed text-ink">
              <p>I&apos;ve always been a friendly person, but I haven&apos;t always been well connected.</p>
              <p>
                Over the years I&apos;ve often found myself navigating a world that assumes everyone has a built-in
                support system.
              </p>
              <ul className="space-y-1">
                <li>Someone to bring along.</li>
                <li>Someone to call.</li>
                <li>Someone to help make decisions.</li>
                <li>Someone to nudge you when life gets stuck.</li>
              </ul>
              <p>Sometimes that&apos;s not the reality.</p>
              <p>One experience that stayed with me was being turned away from a bar because I arrived alone.</p>
              <p>It was a small moment, but it highlighted something much larger:</p>
              <p>Many experiences are still designed around people arriving as part of a pair or group.</p>
              <p>And yet more and more people are building independent lives.</p>
              <p>Not because they&apos;ve given up on connection.</p>
              <p>
                But because they want the freedom to keep living, exploring, learning and growing regardless of who
                happens to be available.
              </p>
              <p>That idea eventually became Go Solo.</p>
            </div>
          </div>
        </div>
      </section>

      <section className="mt-20 max-w-3xl" aria-labelledby="belief-title">
        <h2 id="belief-title" className="font-serif text-4xl tracking-tight text-ink sm:text-5xl">
          Freedom Is A Choice, Not A Circumstance
        </h2>
        <p className="mt-8 text-lg leading-relaxed text-ink sm:text-xl">Many of us postpone parts of our lives while waiting for:</p>
        <ul className="mt-4 space-y-2 text-lg text-ink">
          {WAITING.map((item) => (
            <li key={item}>{item}</li>
          ))}
        </ul>
        <div className="mt-8 space-y-4 text-lg leading-relaxed text-ink sm:text-xl">
          <p>Go Solo is built on a different belief:</p>
          <p>Freedom begins when our lives are no longer dependent on all of those things aligning.</p>
          <p>That doesn&apos;t mean doing everything alone.</p>
          <p>It means knowing your life can keep moving.</p>
        </div>
      </section>

      <section className="mt-20" aria-labelledby="tuesday-title">
        <div className="grid gap-8 lg:grid-cols-2 lg:items-end">
          <div>
            <h2 id="tuesday-title" className="font-serif text-4xl tracking-tight text-ink sm:text-5xl">
              Not Every Story Is An Adventure
            </h2>
            <div className="mt-6 space-y-4 text-lg leading-relaxed text-ink sm:text-xl">
              <p>Sometimes growth looks like taking a trip you&apos;ve postponed for years.</p>
              <p>Sometimes growth looks like learning how to enjoy a quiet Tuesday evening.</p>
              <p>Both matter.</p>
              <p>Go Solo is interested in both.</p>
            </div>
          </div>
          <Panel tone="gold">
            <p className="font-serif text-3xl leading-snug tracking-tight text-ink">The exciting moments.</p>
            <p className="mt-4 font-serif text-3xl leading-snug tracking-tight text-ink">
              And the ordinary moments where life is actually lived.
            </p>
          </Panel>
        </div>
      </section>

      <section className="mt-20" aria-labelledby="hello-title">
        <Panel tone="sage">
          <h2 id="hello-title" className="font-serif text-4xl tracking-tight text-ink sm:text-5xl">
            Say Hello
          </h2>
          <div className="mt-6 max-w-2xl space-y-4 text-lg leading-relaxed text-ink">
            <p>Go Solo is intentionally founder-led.</p>
            <p>
              If you have a question, an idea, feedback, or simply want to say hello, I&apos;d genuinely love to hear
              from you.
            </p>
          </div>
          <dl className="mt-8 space-y-4 text-lg text-ink">
            <div>
              <dt className="text-sm text-ink-soft">Name</dt>
              <dd className="mt-1 font-serif text-3xl tracking-tight">Marge Aliaga</dd>
            </div>
            <div>
              <dt className="text-sm text-ink-soft">Email</dt>
              <dd className="mt-1">
                <a
                  href="mailto:marge@gosolo.co.network"
                  className="text-xl underline decoration-ink/30 underline-offset-4 hover:decoration-ink"
                >
                  marge@gosolo.co.network
                </a>
              </dd>
            </div>
          </dl>
        </Panel>
      </section>

      <section className="mt-20 max-w-3xl" aria-labelledby="chair-title">
        <h2 id="chair-title" className="font-serif text-4xl tracking-tight text-ink sm:text-5xl">
          Pull Up A Chair
        </h2>
        <div className="mt-8 space-y-4 text-lg leading-relaxed text-ink sm:text-xl">
          <p>
            Whether you&apos;re travelling solo, starting over, learning something new, creating routines, building
            friendships, or simply figuring things out one step at a time, you&apos;re welcome here.
          </p>
          <p>Go Solo exists for people building meaningful lives on their own terms.</p>
        </div>
        <p className="mt-10 font-serif text-4xl tracking-tight text-ink sm:text-5xl">Go Solo, Not Alone</p>
      </section>
    </Frame>
  );
}
