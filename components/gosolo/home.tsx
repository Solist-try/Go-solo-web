"use client";

import Link from "next/link";
import { pill } from "@/components/gosolo/pieces";
import { Button } from "@/components/ui/button";
import { useGoSolo } from "@/lib/gosolo";
import { SEED_CATEGORIES } from "@/lib/types";

const phraseTones = ["bg-sage", "bg-clay", "bg-gold"];
const stepTones = ["bg-sage", "bg-clay", "bg-gold", "bg-mist"];

export function HomePage() {
  const { content, seeds, waypoints, desk } = useGoSolo();
  const featuredIds = desk.featuredSeedIds.length
    ? desk.featuredSeedIds
    : ["ticket-for-one", "sunday-reset", "old-friend"];
  const featured = featuredIds
    .map((id) => seeds.find((seed) => seed.id === id))
    .filter((seed) => seed != null)
    .slice(0, 3);
  const phrases = content.phrases.map((text, index) => ({
    text,
    tone: phraseTones[index % phraseTones.length],
  }));
  const steps = content.steps.map((step, index) => ({
    ...step,
    tone: stepTones[index % stepTones.length],
  }));

  return (
    <>
      <section className="mx-auto grid w-full max-w-6xl items-end gap-14 px-5 pt-16 pb-8 sm:px-8 sm:pt-24 lg:grid-cols-12 lg:pt-28">
        <div className="lg:col-span-7">
          <p className="text-sm text-ink-soft">{content.heroTagline}</p>
          <h1 className="mt-6 font-serif text-6xl leading-[0.98] tracking-tight text-balance text-ink sm:text-7xl md:text-8xl">
            {content.heroTitle}
          </h1>
          <p className="mt-6 max-w-xl font-serif text-3xl leading-snug tracking-tight text-ink sm:text-4xl">
            {content.heroSubhead}
          </p>
          <p className="mt-8 max-w-xl text-lg leading-relaxed text-ink">{content.heroSupport}</p>
          {content.heroLede ? (
            <p className="mt-4 max-w-xl text-lg leading-relaxed text-ink-soft">{content.heroLede}</p>
          ) : null}
          <div className="mt-10 flex flex-col gap-3 sm:flex-row">
            <Button asChild className={pill}>
              <Link href="/register">{content.heroPrimary}</Link>
            </Button>
            <Button asChild variant="outline" className={`${pill} bg-transparent`}>
              <Link href="/seeds">{content.heroSecondary}</Link>
            </Button>
          </div>
        </div>
        <div className="flex flex-col gap-4 lg:col-span-5">
          {phrases.map((phrase) => (
            <p
              key={phrase.text}
              className={`${phrase.tone} rounded-[28px] px-7 py-8 font-serif text-3xl leading-tight tracking-tight text-ink sm:text-4xl`}
            >
              {phrase.text}
            </p>
          ))}
        </div>
      </section>

      <section id="sometimes" className="mx-auto w-full max-w-6xl px-5 py-8 sm:px-8">
        <div className="grid gap-4 lg:grid-cols-2">
          <div className="rounded-[28px] bg-gold/70 p-7 sm:p-8">
            <p className="text-lg text-ink">{content.balanceIntro}</p>
            <p className="mt-4 font-serif text-4xl leading-tight tracking-tight text-ink">{content.balanceItems[0]}</p>
          </div>
          <div className="rounded-[28px] bg-sage/80 p-7 sm:p-8">
            <p className="text-lg text-ink">{content.balanceIntro}</p>
            <ul className="mt-4 space-y-2 text-lg text-ink">
              {content.balanceItems.slice(1).map((item) => (
                <li key={item}>{item}</li>
              ))}
            </ul>
          </div>
        </div>
        <p className="mt-8 font-serif text-4xl tracking-tight text-ink sm:text-5xl">{content.balanceClose}</p>
      </section>

      <section id="pillars" className="mx-auto w-full max-w-6xl px-5 py-12 sm:px-8">
        <p className="max-w-2xl text-lg leading-relaxed text-ink">{content.pillarNote}</p>
        <ol className="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
          {content.pillars.map((pillar, index) => (
            <li key={pillar.name} className="rounded-[28px] bg-white/80 p-6 shadow-soft">
              <p className="text-sm text-ink-soft">{index + 1}</p>
              <h2 className="mt-3 font-serif text-3xl tracking-tight text-ink">{pillar.name}</h2>
              <p className="mt-3 text-base leading-relaxed text-ink">{pillar.body}</p>
            </li>
          ))}
        </ol>
      </section>

      <section id="what-is" className="scroll-mt-24 mx-auto w-full max-w-6xl px-5 py-16 sm:px-8 sm:py-20">
        <div className="max-w-3xl">
          <h2 className="font-serif text-4xl leading-tight tracking-tight text-ink sm:text-5xl">{content.whatTitle}</h2>
          <p className="mt-6 text-xl leading-relaxed text-ink sm:text-2xl">{content.whatBody}</p>
        </div>
        <div className="mt-10 grid gap-4 lg:grid-cols-2">
          <div className="rounded-[28px] bg-white/80 p-7 shadow-soft sm:p-8">
            <h3 className="text-lg leading-relaxed text-ink">{content.waitingIntro}</h3>
            <ul className="mt-5 space-y-3 text-lg text-ink">
              {content.waitingFor.map((item) => (
                <li key={item} className="border-t border-ink/10 pt-3">
                  {item}
                </li>
              ))}
            </ul>
          </div>
          <div className="rounded-[28px] bg-sage/80 p-7 sm:p-8">
            <p className="font-serif text-3xl leading-snug tracking-tight text-ink">{content.whatBridge}</p>
            <h3 className="mt-8 text-lg leading-relaxed text-ink">{content.togetherIntro}</h3>
            <ul className="mt-5 space-y-3 text-lg text-ink">
              {content.together.map((item) => (
                <li key={item} className="border-t border-ink/10 pt-3">
                  {item}
                </li>
              ))}
            </ul>
          </div>
        </div>
        <div className="mt-4 rounded-[28px] bg-white/80 p-7 shadow-soft sm:p-8">
          <h3 className="font-serif text-3xl tracking-tight text-ink">{content.whoTitle}</h3>
          <ul className="mt-6 grid gap-3 sm:grid-cols-2">
            {content.who.map((item) => (
              <li key={item} className="text-lg text-ink">
                {item}
              </li>
            ))}
          </ul>
        </div>
        <div className="mt-4 rounded-[28px] bg-mist/80 p-7 sm:p-8">
          <h3 className="font-serif text-3xl tracking-tight text-ink">{content.comeHereTitle}</h3>
          <ul className="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            {content.comeHere.map((item) => (
              <li key={item} className="rounded-[24px] bg-background px-5 py-4 text-lg text-ink">
                {item}
              </li>
            ))}
          </ul>
        </div>
        <div className="mt-4 grid gap-4 lg:grid-cols-2">
          <div className="rounded-[28px] bg-gold/70 p-7 sm:p-8">
            <h3 className="font-serif text-3xl tracking-tight text-ink">{content.expansionTitle}</h3>
            <ul className="mt-5 grid gap-2 sm:grid-cols-2">
              {content.expansion.map((item) => (
                <li key={item} className="text-lg text-ink">
                  {item}
                </li>
              ))}
            </ul>
          </div>
          <div className="rounded-[28px] bg-clay/70 p-7 sm:p-8">
            <h3 className="font-serif text-3xl tracking-tight text-ink">{content.everydayTitle}</h3>
            <ul className="mt-5 grid gap-2 sm:grid-cols-2">
              {content.everyday.map((item) => (
                <li key={item} className="text-lg text-ink">
                  {item}
                </li>
              ))}
            </ul>
          </div>
        </div>
      </section>

      <section id="how-it-works" className="scroll-mt-24 bg-white/50">
        <div className="mx-auto w-full max-w-6xl px-5 py-16 sm:px-8 sm:py-20">
          <h2 className="font-serif text-4xl leading-tight tracking-tight text-ink sm:text-5xl">{content.howTitle}</h2>
          <p className="mt-4 max-w-2xl text-xl leading-relaxed text-ink">{content.howIntro}</p>
          <ol className="mt-10 grid gap-4 sm:grid-cols-2">
            {steps.map((step, index) => (
              <li key={step.name} className={`${step.tone} rounded-[28px] px-7 py-8`}>
                <p className="text-sm text-ink-soft">Step {index + 1}</p>
                <p className="mt-4 text-3xl" aria-hidden="true">
                  {step.mark}
                </p>
                <h3 className="mt-4 font-serif text-3xl tracking-tight text-ink">{step.name}</h3>
                <p className="mt-3 text-lg leading-relaxed text-ink">{step.body}</p>
                {step.lines.length > 0 ? (
                  <div className="mt-5">
                    {step.linesLabel ? <p className="text-sm text-ink-soft">{step.linesLabel}</p> : null}
                    <ul className={`space-y-2 text-lg text-ink ${step.linesLabel ? "mt-2" : ""}`}>
                      {step.lines.map((line) => (
                        <li key={line}>{line}</li>
                      ))}
                    </ul>
                  </div>
                ) : null}
                {step.href ? (
                  <p className="mt-6">
                    <Link href={step.href} className="text-base underline decoration-ink/20 underline-offset-4">
                      {step.hrefLabel || step.name}
                    </Link>
                  </p>
                ) : null}
              </li>
            ))}
          </ol>
        </div>
      </section>

      <section id="philosophy" className="scroll-mt-24 mx-auto grid w-full max-w-6xl gap-10 px-5 py-20 sm:px-8 lg:grid-cols-12 lg:py-28">
        <div className="lg:col-span-5">
          <h2 className="font-serif text-4xl leading-tight tracking-tight text-ink sm:text-5xl">
            {content.philosophyTitle}
          </h2>
        </div>
        <div className="space-y-6 text-lg leading-relaxed text-ink lg:col-span-6 lg:col-start-7">
          {content.philosophyParagraphs.map((paragraph) => (
            <p key={paragraph}>{paragraph}</p>
          ))}
          <p className="font-serif text-3xl leading-snug tracking-tight text-ink">{content.philosophyClose}</p>
        </div>
      </section>

      <section className="mx-auto w-full max-w-6xl px-5 pb-8 sm:px-8">
        <p className="max-w-3xl font-serif text-4xl leading-tight tracking-tight text-ink sm:text-6xl">
          {content.belief}
          <span className="mt-3 block text-ink-soft">{content.beliefSecond}</span>
        </p>
      </section>

      <section id="seeds" className="scroll-mt-24 mx-auto w-full max-w-6xl px-5 py-20 sm:px-8 lg:py-28">
        <div className="flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
          <div className="max-w-2xl">
            <h2 className="font-serif text-4xl leading-tight tracking-tight text-ink sm:text-5xl">Seeds</h2>
            <p className="mt-4 text-lg leading-relaxed text-ink-soft">
              {content.seedsIntro}
            </p>
          </div>
          <Button asChild variant="outline" className={`${pill} bg-transparent`}>
            <Link href="/seeds">Browse the seeds</Link>
          </Button>
        </div>
        <ul className="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {SEED_CATEGORIES.map((category) => (
            <li key={category.id} className="rounded-[28px] bg-sage/70 p-6">
              <h3 className="font-serif text-2xl text-ink">{category.label}</h3>
              <p className="mt-3 text-base leading-relaxed text-ink-soft">{category.line}</p>
            </li>
          ))}
        </ul>
        <ul className="mt-4 grid gap-4 lg:grid-cols-3">
          {featured.map((seed) =>
            seed ? (
              <li key={seed.id}>
                <Link href={`/seeds/${seed.id}`} className="block h-full rounded-[28px] bg-white/80 p-6 shadow-soft">
                  <p className="text-sm text-ink-soft">{seed.timeframe}</p>
                  <h3 className="mt-3 font-serif text-3xl leading-tight tracking-tight text-ink">{seed.title}</h3>
                  <p className="mt-4 leading-relaxed text-ink-soft">{seed.description}</p>
                </Link>
              </li>
            ) : null,
          )}
        </ul>
      </section>

      <section id="out-there" className="scroll-mt-24 bg-clay/60">
        <div className="mx-auto grid w-full max-w-6xl gap-10 px-5 py-20 sm:px-8 lg:grid-cols-12 lg:py-28">
          <div className="lg:col-span-4">
            <h2 className="font-serif text-4xl leading-tight tracking-tight text-ink sm:text-5xl">Out There</h2>
            <p className="mt-4 text-lg leading-relaxed text-ink">
              Real-world experiences. Not achievements. Not success stories. What you did, what you
              expected, what happened, and whether you would do it again.
            </p>
          </div>
          <article className="rounded-[28px] bg-background p-7 shadow-soft sm:p-10 lg:col-span-8">
            <p className="text-sm text-ink-soft">An experience, told plainly</p>
            <h3 className="mt-4 font-serif text-4xl leading-tight tracking-tight text-ink">
              The museum on a Wednesday
            </h3>
            <dl className="mt-8 space-y-6">
              <div>
                <dt className="text-sm text-ink-soft">What did you do?</dt>
                <dd className="mt-1 text-lg leading-relaxed">
                  Went to the tile museum alone after work and stayed until they started closing the rooms.
                </dd>
              </div>
              <div>
                <dt className="text-sm text-ink-soft">What were you expecting?</dt>
                <dd className="mt-1 text-lg leading-relaxed">
                  To feel conspicuous and leave after twenty minutes.
                </dd>
              </div>
              <div>
                <dt className="text-sm text-ink-soft">What actually happened?</dt>
                <dd className="mt-1 text-lg leading-relaxed">
                  Nobody noticed. One blue wall was enough. A sentence arrived on a receipt.
                </dd>
              </div>
              <div>
                <dt className="text-sm text-ink-soft">Would you do it again?</dt>
                <dd className="mt-1 text-lg">Yes.</dd>
              </div>
            </dl>
            <p className="mt-8 text-sm text-ink-soft">
              People can respond with Inspired me, I relate, or Interesting perspective. There are no
              like counts and no rankings.
            </p>
          </article>
        </div>
      </section>

      <section id="campfire" className="scroll-mt-24 mx-auto grid w-full max-w-6xl gap-10 px-5 py-20 sm:px-8 lg:grid-cols-12 lg:py-28">
        <div className="lg:col-span-6">
          <h2 className="font-serif text-4xl leading-tight tracking-tight text-ink sm:text-5xl">
            Pull up a chair.
          </h2>
          <p className="mt-4 font-serif text-3xl leading-snug tracking-tight text-ink">
            What&apos;s on your mind?
          </p>
          <p className="mt-6 max-w-xl text-lg leading-relaxed text-ink-soft">
            Campfire is the conversational layer. Questions, thoughts, reflections, daily life,
            stories, celebrations, and challenges. The point is companionship, not information
            exchange. No upvotes. No popularity.
          </p>
        </div>
        <div className="space-y-4 lg:col-span-6">
          <article className="rounded-[28px] bg-gold p-7">
            <p className="text-sm text-ink-soft">A question</p>
            <h3 className="mt-3 font-serif text-3xl leading-tight text-ink">
              Do you tell people you are going alone?
            </h3>
            <p className="mt-4 leading-relaxed text-ink">
              I do not want a speech. I also do not want to apologize. What do you actually say?
            </p>
          </article>
          <article className="rounded-[28px] bg-white/80 p-7">
            <p className="text-sm text-ink-soft">A reply</p>
            <p className="mt-3 text-lg leading-relaxed text-ink">
              I say just me, and then I ask them something. It ends the speech before it starts.
            </p>
          </article>
          <ul className="grid grid-cols-2 gap-3 text-sm text-ink-soft sm:grid-cols-4">
            {["General", "Growing", "Solo Living", "Deep Thoughts"].map((section) => (
              <li key={section} className="rounded-full bg-mist px-4 py-3 text-center text-ink">
                {section}
              </li>
            ))}
          </ul>
        </div>
      </section>

      <section id="waypoints" className="scroll-mt-24 bg-mist/80">
        <div className="mx-auto w-full max-w-6xl px-5 py-20 sm:px-8 lg:py-28">
          <div className="max-w-2xl">
            <h2 className="font-serif text-4xl leading-tight tracking-tight text-ink sm:text-5xl">Waypoints</h2>
            <p className="mt-4 text-lg leading-relaxed text-ink-soft">
              Places where people exploring similar parts of life gather, share experiences, and
              continue their journey.
            </p>
          </div>
          <ul className="mt-10 grid gap-4 lg:grid-cols-3">
            {waypoints.map((waypoint) => (
              <li key={waypoint.id}>
                <Link href={`/waypoints/${waypoint.slug}`} className="block h-full rounded-[28px] bg-background p-7 shadow-soft">
                  <h3 className="font-serif text-3xl leading-tight tracking-tight text-ink">{waypoint.name}</h3>
                  <p className="mt-4 leading-relaxed text-ink-soft">{waypoint.description}</p>
                  <p className="mt-6 text-sm text-ink">{waypoint.focus.join(" · ")}</p>
                </Link>
              </li>
            ))}
          </ul>
        </div>
      </section>

      <section id="coming-soon" className="scroll-mt-24 mx-auto w-full max-w-6xl px-5 py-20 sm:px-8 lg:py-28">
        <h2 className="font-serif text-4xl leading-tight tracking-tight text-ink sm:text-5xl">Coming later</h2>
        <p className="mt-4 max-w-2xl text-lg leading-relaxed text-ink-soft">
          {content.comingSoonIntro}
        </p>
        <ul className="mt-10 grid gap-4 lg:grid-cols-3">
          {content.comingSoon.map((item) => (
            <li key={item.title} className="rounded-[28px] border border-dashed border-ink/15 bg-white/40 p-7">
              <p className="text-sm text-ink-soft">Coming soon</p>
              <h3 className="mt-3 font-serif text-3xl text-ink">{item.title}</h3>
              <p className="mt-4 leading-relaxed text-ink-soft">{item.body}</p>
              <button
                type="button"
                disabled
                className="mt-6 h-12 cursor-not-allowed rounded-full bg-mist px-5 text-sm text-ink-soft"
              >
                Not open yet
              </button>
            </li>
          ))}
        </ul>
      </section>
    </>
  );
}
