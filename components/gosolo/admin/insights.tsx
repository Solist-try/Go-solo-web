"use client";

import Link from "next/link";
import { DeskIntro, ShareList } from "@/components/gosolo/admin/ui";
import { buildInsights } from "@/lib/admin/insights";
import { formatDate } from "@/lib/format";
import { useGoSolo } from "@/lib/gosolo";

export function InsightsPage() {
  const { library, seeds, waypoints } = useGoSolo();
  const insight = buildInsights({ world: library, seeds, waypoints });

  return (
    <>
      <DeskIntro eyebrow="Insights" title="How people actually use Go Solo.">
        Onboarding, interests, waypoints, seeds, and whether people come back. The health score explains itself.
      </DeskIntro>

      <section className="mt-12 max-w-3xl">
        <h2 className="font-serif text-4xl text-ink">Why people joined</h2>
        <p className="mt-3 text-ink-soft">Each person can carry more than one reason. Percentages share the reasons that were named.</p>
        <div className="mt-6">
          <ShareList rows={insight.joined} empty="No onboarding reasons yet." />
        </div>
      </section>

      <section className="mt-14 max-w-3xl">
        <h2 className="font-serif text-4xl text-ink">Top interests</h2>
        <div className="mt-6">
          <ShareList rows={insight.interests} empty="No interests yet." />
        </div>
      </section>

      <section className="mt-14">
        <h2 className="font-serif text-4xl text-ink">Most joined waypoints</h2>
        <ul className="mt-6 space-y-4">
          {insight.joinedWaypoints.map((item) => (
            <li key={item.waypoint.id} className="flex items-baseline justify-between gap-4">
              <Link href={`/admin/waypoints/${item.waypoint.id}`} className="font-serif text-2xl text-ink">
                {item.waypoint.name}
              </Link>
              <span className="text-ink-soft">{item.members} members</span>
            </li>
          ))}
        </ul>
      </section>

      <section className="mt-14 grid gap-10 lg:grid-cols-2">
        <div>
          <h2 className="font-serif text-4xl text-ink">Most started seeds</h2>
          <ul className="mt-6 space-y-3">
            {insight.startedSeeds.length === 0 ? <li className="text-ink-soft">None yet.</li> : null}
            {insight.startedSeeds.map((item) => (
              <li key={item.seed.id} className="flex justify-between gap-4">
                <span>{item.seed.title}</span>
                <span className="text-ink-soft">{item.starts}</span>
              </li>
            ))}
          </ul>
        </div>
        <div>
          <h2 className="font-serif text-4xl text-ink">Most completed seeds</h2>
          <ul className="mt-6 space-y-3">
            {insight.completedSeeds.length === 0 ? <li className="text-ink-soft">None completed yet.</li> : null}
            {insight.completedSeeds.map((item) => (
              <li key={item.seed.id} className="flex justify-between gap-4">
                <span>{item.seed.title}</span>
                <span className="text-ink-soft">{item.completions}</span>
              </li>
            ))}
          </ul>
        </div>
      </section>

      <section className="mt-14 max-w-3xl">
        <h2 className="font-serif text-4xl text-ink">Top Out There categories</h2>
        <div className="mt-6">
          <ShareList rows={insight.storyCategories} empty="No stories yet." />
        </div>
      </section>

      <section className="mt-14">
        <h2 className="font-serif text-4xl text-ink">Retention overview</h2>
        <p className="mt-3 max-w-2xl text-ink-soft">
          Of the people who have had enough time, how many did something again after that many days.
        </p>
        <ul className="mt-6 grid gap-4 sm:grid-cols-3">
          {insight.retention.map((item) => (
            <li key={item.days} className="rounded-[28px] bg-mist p-6">
              <p className="text-sm text-ink-soft">Returned after {item.days} days</p>
              <p className="mt-3 font-serif text-5xl text-ink">{item.percent}%</p>
              <p className="mt-3 text-sm leading-relaxed text-ink">
                {item.returned} of {item.eligible} eligible {item.eligible === 1 ? "person" : "people"}.
              </p>
            </li>
          ))}
        </ul>
      </section>

      <section className="mt-14 rounded-[28px] bg-gold p-7 sm:p-10">
        <p className="text-sm text-ink-soft">Room health score</p>
        <p className="mt-3 font-serif text-7xl tracking-tight text-ink">{insight.health.score}</p>
        <p className="mt-4 max-w-2xl font-serif text-3xl leading-snug text-ink">{insight.health.reading}</p>
        <ul className="mt-8 space-y-5">
          {insight.health.parts.map((part) => (
            <li key={part.label}>
              <p className="text-lg text-ink">
                {part.label}
                <span className="text-ink-soft"> · {part.score} of {part.of}</span>
              </p>
              <p className="mt-1 max-w-2xl leading-relaxed text-ink">{part.detail}</p>
            </li>
          ))}
        </ul>
      </section>

      <p className="mt-10 text-sm text-ink-soft">
        Dates below use the room as it is on {formatDate(new Date().toISOString())}.
      </p>
    </>
  );
}
