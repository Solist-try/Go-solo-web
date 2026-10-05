"use client";

import Image from "next/image";
import Link from "next/link";
import { pill } from "@/components/gosolo/pieces";
import { Button } from "@/components/ui/button";
import { useGoSolo } from "@/lib/gosolo";

const phraseTones = ["bg-sage", "bg-clay", "bg-gold"];
const stepTones = ["bg-sage", "bg-clay", "bg-gold", "bg-mist"];

export function HomePage() {
  const { content, waypoints } = useGoSolo();
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
      <section className="mx-auto w-full max-w-6xl px-5 pt-16 pb-8 sm:px-8 sm:pt-24">
        <h1 className="font-serif text-[clamp(1.75rem,8.2vw,5.25rem)] leading-none tracking-tight whitespace-nowrap text-ink">
          {content.heroTagline}
        </h1>
        <div className="mt-10 grid items-end gap-8 lg:grid-cols-12">
          <div className="lg:col-span-7">
            <p className="font-serif text-[1.65rem] leading-tight tracking-tight text-ink sm:text-5xl">{content.heroTitle}</p>
            <p className="mt-3 font-serif text-2xl leading-snug tracking-tight text-ink sm:text-4xl">{content.heroSubhead}</p>
            <p className="mt-6 max-w-xl text-lg leading-relaxed text-ink">{content.heroSupport}</p>
          </div>
          <div className="flex flex-col gap-4 lg:col-span-5">
            {phrases.map((phrase) => (
              <p
                key={phrase.text}
                className={`${phrase.tone} rounded-[28px] px-7 py-7 font-serif text-3xl leading-tight tracking-tight text-ink sm:text-4xl`}
              >
                {phrase.text}
              </p>
            ))}
          </div>
        </div>
        <div className="mt-10 flex flex-col gap-3 sm:flex-row">
          <Button asChild className={pill}>
            <Link href="/register">{content.heroPrimary}</Link>
          </Button>
          <Button asChild variant="outline" className={`${pill} bg-transparent`}>
            <a href="#how-it-works">{content.heroSecondary}</a>
          </Button>
        </div>
        <figure className="mt-14">
          <Image
            src="/hero-city-walk.jpg"
            alt="A person walking home along a rainy street with groceries."
            width={1280}
            height={720}
            priority
            sizes="(min-width: 72rem) 72rem, 100vw"
            className="aspect-video w-full rounded-[28px] object-cover"
          />
        </figure>
      </section>

      <section id="freedom" className="mx-auto w-full max-w-6xl px-5 py-12 sm:px-8 sm:py-16">
        <div className="grid items-center gap-8 lg:grid-cols-12">
          <div className="lg:col-span-4">
            <h2 className="font-serif text-4xl leading-tight tracking-tight text-ink sm:text-5xl">Freedom is a choice.</h2>
            <p className="mt-4 max-w-sm text-lg leading-relaxed text-ink">A quiet morning at home is part of an independent life.</p>
          </div>
          <figure className="lg:col-span-8">
            <Image
              src="/freedom-at-home.jpg"
              alt="A person pouring tea at a wooden table in a small apartment."
              width={1152}
              height={864}
              sizes="(min-width: 72rem) 48rem, 100vw"
              className="aspect-[4/3] w-full rounded-[28px] object-cover"
            />
          </figure>
        </div>
      </section>

      <section className="mx-auto w-full max-w-6xl px-5 py-10 sm:px-8">
        <p className="text-lg text-ink">{content.balanceIntro}</p>
        <ul className="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          {content.balanceItems.map((item) => (
            <li key={item} className="rounded-[24px] bg-white/80 px-5 py-4 font-serif text-2xl leading-tight text-ink">
              {item}
            </li>
          ))}
        </ul>
      </section>

      <section id="how-it-works" className="scroll-mt-24 bg-white/50">
        <div className="mx-auto w-full max-w-6xl px-5 py-16 sm:px-8 sm:py-20">
          <h2 className="font-serif text-4xl leading-tight tracking-tight text-ink sm:text-5xl">{content.howTitle}</h2>
          <ol className="mt-8 grid gap-4 sm:grid-cols-2">
            {steps.map((step) => (
              <li key={step.name} className={`${step.tone} rounded-[28px] px-7 py-8`}>
                <p className="text-3xl" aria-hidden="true">
                  {step.mark}
                </p>
                <h3 className="mt-4 font-serif text-3xl tracking-tight text-ink">{step.name}</h3>
                <p className="mt-3 text-lg leading-relaxed text-ink">{step.body}</p>
                {step.href ? (
                  <p className="mt-5">
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

      <section id="seeds" className="scroll-mt-24 mx-auto w-full max-w-6xl px-5 py-16 sm:px-8 sm:py-20">
        <div className="flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
          <div className="max-w-xl">
            <h2 className="font-serif text-4xl leading-tight tracking-tight text-ink sm:text-5xl">Seeds</h2>
            <p className="mt-3 text-lg leading-relaxed text-ink-soft">Tiny futures you can plant now.</p>
          </div>
          <Button asChild variant="outline" className={`${pill} bg-transparent`}>
            <Link href="/seeds">See the seeds</Link>
          </Button>
        </div>
        <ul className="mt-8 grid gap-4 lg:grid-cols-2">
          <li>
            <Link href="/seeds/weekly-hello" className="block h-full rounded-[28px] bg-sage p-6 shadow-soft sm:p-8">
              <h3 className="font-serif text-3xl leading-tight tracking-tight text-ink">SAME</h3>
              <p className="mt-4 leading-relaxed text-ink">
                A seed grows faster when someone else helps tend it.
              </p>
            </Link>
          </li>
          <li>
            <Link href="/seeds/skill-swap" className="block h-full rounded-[28px] bg-clay p-6 shadow-soft sm:p-8">
              <h3 className="font-serif text-3xl leading-tight tracking-tight text-ink">Skill Swap</h3>
              <p className="mt-4 leading-relaxed text-ink">Learn something. Teach something.</p>
            </Link>
          </li>
        </ul>
      </section>

      <section id="out-there" className="scroll-mt-24 bg-clay/60">
        <div className="mx-auto grid w-full max-w-6xl gap-8 px-5 py-16 sm:px-8 lg:grid-cols-12 lg:py-20">
          <div className="lg:col-span-4">
            <h2 className="font-serif text-4xl leading-tight tracking-tight text-ink sm:text-5xl">Out There</h2>
            <p className="mt-3 text-lg leading-relaxed text-ink">What happened, told plainly.</p>
          </div>
          <div className="rounded-[28px] bg-background p-7 sm:p-8 lg:col-span-8">
            <p className="font-serif text-3xl leading-snug tracking-tight text-ink">
              A story here is something a member actually did.
            </p>
            <p className="mt-4 max-w-xl text-lg leading-relaxed text-ink">
              Ideas for a first step sit on the same page, and they are marked as ideas.
            </p>
            <p className="mt-6">
              <Link href="/out-there" className="text-lg underline decoration-ink/20 underline-offset-4">
                See Out There
              </Link>
            </p>
          </div>
        </div>
      </section>

      <section id="campfire" className="scroll-mt-24 mx-auto grid w-full max-w-6xl gap-8 px-5 py-16 sm:px-8 lg:grid-cols-2 lg:py-20">
        <div>
          <h2 className="font-serif text-4xl leading-tight tracking-tight text-ink sm:text-5xl">Pull up a chair.</h2>
          <p className="mt-3 font-serif text-3xl leading-snug tracking-tight text-ink">What&apos;s on your mind?</p>
        </div>
        <div className="space-y-4">
          <article className="rounded-[28px] bg-gold p-7">
            <h3 className="font-serif text-3xl leading-tight text-ink">Do you tell people you are going alone?</h3>
            <p className="mt-4 leading-relaxed text-ink">I say just me, and then I ask them something.</p>
          </article>
          <ul className="grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
            {["General", "Growing", "Solo Living", "Deep Thoughts"].map((section) => (
              <li key={section} className="rounded-full bg-mist px-4 py-3 text-center text-ink">
                {section}
              </li>
            ))}
          </ul>
        </div>
      </section>

      <section id="waypoints" className="scroll-mt-24 bg-mist/80">
        <div className="mx-auto w-full max-w-6xl px-5 py-16 sm:px-8 lg:py-20">
          <h2 className="font-serif text-4xl leading-tight tracking-tight text-ink sm:text-5xl">Waypoints</h2>
          <ul className="mt-8 grid gap-4 lg:grid-cols-3">
            {waypoints.map((waypoint) => (
              <li key={waypoint.id}>
                <Link href={`/waypoints/${waypoint.slug}`} className="block h-full rounded-[28px] bg-background p-7 shadow-soft">
                  <h3 className="font-serif text-3xl leading-tight tracking-tight text-ink">{waypoint.name}</h3>
                  <p className="mt-3 leading-relaxed text-ink-soft">{waypoint.summary}</p>
                </Link>
              </li>
            ))}
          </ul>
        </div>
      </section>
    </>
  );
}
