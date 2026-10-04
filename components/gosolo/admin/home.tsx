"use client";

import Link from "next/link";
import { DeskIntro, Stat } from "@/components/gosolo/admin/ui";
import { buildInsights } from "@/lib/admin/insights";
import { useGoSolo } from "@/lib/gosolo";

export function AdminHome() {
  const { library, seeds, waypoints, desk } = useGoSolo();
  const insight = buildInsights({ world: library, seeds, waypoints });
  const cards = [
    ["Total members", insight.members, "People in the room, not counting the steward."],
    ["New members this week", insight.newMembers, "Joined in the last seven days."],
    ["Weekly active members", insight.weeklyActive, "Anyone who did something here in the last seven days."],
    ["New Out There posts", insight.newStories, "Stories brought back this week."],
    ["New Campfire posts", insight.newCampfire, "What was said by the fire this week."],
    ["Seeds started", insight.seedsStarted, `${insight.seedsStartedThisWeek} began this week.`],
    ["Seeds completed", insight.seedsCompleted, "Finished, not merely opened."],
    ["SAME matches", insight.sameMatches, "Pairs who found each other."],
    ["Skill swaps completed", insight.skillSwaps, "An offer and a person who reached across."],
  ] as const;

  return (
    <>
      <DeskIntro eyebrow="Steward desk" title="How is the room?">
        A high look at health. Counts stay in sentences. Nothing here is a scoreboard for members.
      </DeskIntro>
      <div className="mt-10 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        {cards.map(([label, value, detail], index) => (
          <Stat key={label} label={label} value={value} detail={detail} index={index} />
        ))}
      </div>
      <div className="mt-12 grid gap-10 lg:grid-cols-3">
        <section>
          <h2 className="font-serif text-3xl text-ink">Most active waypoints</h2>
          <ul className="mt-4 space-y-3">
            {insight.waypoints.map((item) => (
              <li key={item.waypoint.id}>
                <Link href={`/admin/waypoints/${item.waypoint.id}`} className="text-lg text-ink underline decoration-ink/15 underline-offset-4">
                  {item.waypoint.name}
                </Link>
                <p className="text-sm text-ink-soft">
                  {item.members} {item.members === 1 ? "member" : "members"} · {item.recent} recent notes
                </p>
              </li>
            ))}
          </ul>
        </section>
        <section>
          <h2 className="font-serif text-3xl text-ink">Most used seeds</h2>
          <ul className="mt-4 space-y-3">
            {insight.usedSeeds.length === 0 ? <li className="text-ink-soft">No seeds have been started.</li> : null}
            {insight.usedSeeds.map((item) => (
              <li key={item.seed.id}>
                <Link href={`/admin/seeds/${item.seed.id}`} className="text-lg text-ink underline decoration-ink/15 underline-offset-4">
                  {item.seed.title}
                </Link>
                <p className="text-sm text-ink-soft">
                  {item.starts} {item.starts === 1 ? "start" : "starts"}
                </p>
              </li>
            ))}
          </ul>
        </section>
        <section>
          <h2 className="font-serif text-3xl text-ink">Top Campfire topics</h2>
          <ul className="mt-4 space-y-3">
            {insight.topics.length === 0 ? <li className="text-ink-soft">The fire is quiet.</li> : null}
            {insight.topics.map((topic) => (
              <li key={topic.id} className="text-lg text-ink">
                {topic.label}
                <span className="mt-1 block text-sm text-ink-soft">{topic.percent}% of posts</span>
              </li>
            ))}
          </ul>
        </section>
      </div>
      <section className="mt-14 max-w-2xl rounded-[28px] bg-gold p-7">
        <p className="text-sm text-ink-soft">Room health · {insight.health.score}</p>
        <p className="mt-3 font-serif text-3xl leading-snug text-ink">{insight.health.reading}</p>
        <Link href="/admin/insights" className="mt-6 inline-block underline decoration-ink/20 underline-offset-4">
          Read the full insight
        </Link>
      </section>
      <section className="mt-12">
        <h2 className="font-serif text-3xl text-ink">Recent care</h2>
        {desk.journal.length === 0 ? (
          <p className="mt-3 text-ink-soft">Nothing has been changed from this desk yet.</p>
        ) : (
          <ul className="mt-4 space-y-2 text-ink">
            {desk.journal.slice(0, 5).map((entry) => (
              <li key={entry.id}>{entry.text}</li>
            ))}
          </ul>
        )}
      </section>
    </>
  );
}
