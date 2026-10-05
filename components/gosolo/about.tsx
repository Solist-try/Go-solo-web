"use client";

import { Frame, Panel } from "@/components/gosolo/pieces";
import { useGoSolo } from "@/lib/gosolo";

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
          <p>I kept noticing the same thing.</p>
          <p>
            A lot of life is still set up for couples, families, and people who already have a circle. I was moving
            through it on my own. Sometimes by choice. Sometimes by circumstance.
          </p>
        </div>
      </header>

      <section className="mt-16" aria-labelledby="founder-title">
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
              <p>I&apos;ve always been a friendly person. I haven&apos;t always been well connected.</p>
              <p>
                People assumed I had someone built in. Someone to bring along, someone to call, someone to help me
                decide, or someone to nudge me when life got stuck. Sometimes I did. Often I didn&apos;t.
              </p>
              <p>
                One experience stayed with me. I was turned away from a bar because I arrived alone. It was a small
                moment, and it made something obvious: a lot of rooms are still built for people who show up as a pair.
              </p>
              <p>
                I hadn&apos;t given up on company. I wanted to keep going when nobody was free to come with me. That is
                how Go Solo started.
              </p>
            </div>
          </div>
        </div>
      </section>

      <section className="mt-16 max-w-2xl" aria-labelledby="belief-title">
        <h2 id="belief-title" className="font-serif text-3xl tracking-tight text-ink sm:text-4xl">
          Freedom Is A Choice, Not A Circumstance
        </h2>
        <div className="mt-6 space-y-4 text-lg leading-relaxed text-ink">
          <p>I postponed a lot while I waited for the right person, the right timing, or a bit more confidence.</p>
          <p>My life can keep moving before all of that lines up. That doesn&apos;t mean doing everything alone.</p>
        </div>
      </section>

      <section className="mt-16 max-w-2xl" aria-labelledby="tuesday-title">
        <h2 id="tuesday-title" className="font-serif text-3xl tracking-tight text-ink sm:text-4xl">
          Not Every Story Is An Adventure
        </h2>
        <p className="mt-6 text-lg leading-relaxed text-ink">
          Sometimes the thing I&apos;d put off was a trip. Sometimes it was a quiet Tuesday evening. I care about both.
        </p>
      </section>

      <section className="mt-16" aria-labelledby="hello-title">
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

      <section className="mt-16 max-w-3xl" aria-labelledby="chair-title">
        <h2 id="chair-title" className="font-serif text-4xl tracking-tight text-ink sm:text-5xl">
          Pull Up A Chair
        </h2>
        <p className="mt-6 text-lg leading-relaxed text-ink sm:text-xl">You&apos;re welcome here.</p>
        <p className="mt-8 font-serif text-4xl tracking-tight text-ink sm:text-5xl">Go Solo, Not Alone</p>
      </section>
    </Frame>
  );
}
