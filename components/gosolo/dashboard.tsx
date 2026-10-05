"use client";

import Link from "next/link";
import { AuthorLine, Frame, TextLink } from "@/components/gosolo/pieces";
import { useCatalog, useGoSolo } from "@/lib/gosolo";
import { formatRelative } from "@/lib/format";
import { EVERYDAY_ACTIONS, EXPANSION_ACTIONS } from "@/lib/suggest";

export function Dashboard() {
  const { user, world } = useGoSolo();
  const { getSeed, getWaypoint } = useCatalog();
  if (!user) return null;
  const names = new Map(world.profiles.map((profile) => [profile.id, profile]));
  const active = world.userSeeds
    .filter((seed) => seed.userId === user.id && seed.status === "active")
    .sort((a, b) => (a.startedAt < b.startedAt ? 1 : -1));
  const current = active[0];
  const currentSeed = current ? getSeed(current.seedId) : undefined;
  const stories = world.stories.filter((story) => !story.example && !story.hidden).slice(0, 2);
  const fires = world.campfire.slice(0, 2);
  const mine = world.memberships.filter((item) => item.userId === user.id);

  return (
    <Frame>
      <p className="text-sm text-ink-soft">Your home</p>
      <h1 className="mt-3 max-w-4xl font-serif text-6xl leading-[0.98] tracking-tight text-balance text-ink sm:text-7xl">
        Hello, Vagabond.
      </h1>
      <p className="mt-6 max-w-2xl font-serif text-3xl leading-snug tracking-tight text-ink sm:text-4xl">
        What&apos;s calling to you today?
      </p>

      <section className="mt-20 max-w-3xl">
        <h2 className="font-serif text-3xl tracking-tight text-ink sm:text-4xl">Current Seed</h2>
        {currentSeed ? (
          <div className="mt-6">
            <Link href={`/seeds/${currentSeed.id}`} className="font-serif text-4xl leading-tight tracking-tight text-ink">
              {current?.title || currentSeed.title}
            </Link>
            <p className="mt-4 max-w-xl text-lg leading-relaxed text-ink-soft">{currentSeed.prompt}</p>
          </div>
        ) : (
          <div className="mt-6">
            <p className="max-w-xl text-lg leading-relaxed text-ink">
              Nothing is planted yet. A seed is a small possibility, waiting for you to try it.
            </p>
            <div className="mt-6">
              <TextLink href="/seeds">Find a Seed</TextLink>
            </div>
          </div>
        )}
      </section>

      <section className="mt-20">
        <div className="flex items-end justify-between gap-4">
          <h2 className="font-serif text-3xl tracking-tight text-ink sm:text-4xl">Recent Out There Stories</h2>
          <TextLink href="/out-there">All stories</TextLink>
        </div>
        <ul className="mt-8 space-y-10">
          {stories.length === 0 ? (
            <li className="text-lg text-ink-soft">No stories yet. The world is still large.</li>
          ) : (
            stories.map((story) => (
              <li key={story.id}>
                <AuthorLine
                  profile={names.get(story.authorId)}
                  meta={formatRelative(story.createdAt)}
                  href={`/profile/${story.authorId}`}
                />
                <Link href={`/out-there/${story.id}`} className="mt-4 block max-w-3xl font-serif text-4xl leading-tight tracking-tight text-ink">
                  {story.title}
                </Link>
              </li>
            ))
          )}
        </ul>
      </section>

      <section className="mt-20 max-w-3xl">
        <div className="flex items-end justify-between gap-4">
          <h2 className="font-serif text-3xl tracking-tight text-ink sm:text-4xl">Campfire Activity</h2>
          <TextLink href="/campfire">Pull up a chair</TextLink>
        </div>
        <ul className="mt-8 space-y-8">
          {fires.length === 0 ? (
            <li className="text-lg text-ink-soft">The chairs are here. The first thing said can be ordinary.</li>
          ) : (
            fires.map((post) => (
              <li key={post.id}>
                <Link href={`/campfire/${post.id}`} className="font-serif text-3xl leading-tight tracking-tight text-ink">
                  {post.title}
                </Link>
                <p className="mt-3 line-clamp-2 text-lg leading-relaxed text-ink-soft">{post.body}</p>
              </li>
            ))
          )}
        </ul>
      </section>

      <section className="mt-20 max-w-3xl">
        <h2 className="font-serif text-3xl tracking-tight text-ink sm:text-4xl">Your Waypoints</h2>
        {mine.length > 0 ? (
          <ul className="mt-8 space-y-4">
            {mine.map((item) => {
              const waypoint = getWaypoint(item.waypointId);
              if (!waypoint) return null;
              return (
                <li key={item.waypointId}>
                  <Link href={`/waypoints/${waypoint.slug}`} className="font-serif text-3xl tracking-tight text-ink">
                    {waypoint.name}
                  </Link>
                </li>
              );
            })}
          </ul>
        ) : (
          <p className="mt-6 text-lg leading-relaxed text-ink">
            You have not chosen a waypoint yet.{" "}
            <TextLink href="/waypoints">Visit a Waypoint</TextLink>
          </p>
        )}
      </section>

      <section className="mt-20" aria-labelledby="next-actions">
        <h2 id="next-actions" className="font-serif text-3xl tracking-tight text-ink sm:text-4xl">
          Suggested next actions
        </h2>
        <p className="mt-4 max-w-2xl text-lg leading-relaxed text-ink-soft">
          A life grows on a trip and on a Tuesday. Pick one from each when you can.
        </p>
        <div className="mt-8 grid gap-4 lg:grid-cols-2">
          <ActionLane title="Out in the world" tone="bg-gold/70" actions={EXPANSION_ACTIONS} />
          <ActionLane title="In ordinary life" tone="bg-sage/80" actions={EVERYDAY_ACTIONS} />
        </div>
      </section>

      <div className="mt-20 max-w-xl">
        <p className="text-sm text-ink-soft">Later, not now</p>
        <ul className="mt-3 space-y-2 text-ink-soft">
          <li>
            <Link href="/experiences" className="underline decoration-ink/15 underline-offset-4">
              Experiences
            </Link>
          </li>
          <li>
            <Link href="/partnerships" className="underline decoration-ink/15 underline-offset-4">
              Partnerships
            </Link>
          </li>
          <li>
            <Link href="/first-night-kits" className="underline decoration-ink/15 underline-offset-4">
              First Night Kits
            </Link>
          </li>
        </ul>
      </div>
    </Frame>
  );
}

function ActionLane({
  title,
  tone,
  actions,
}: {
  title: string;
  tone: string;
  actions: { title: string; body: string; href: string }[];
}) {
  return (
    <div className={`rounded-[28px] p-7 ${tone}`}>
      <h3 className="font-serif text-3xl tracking-tight text-ink">{title}</h3>
      <ul className="mt-6 space-y-5">
        {actions.map((action) => (
          <li key={action.href}>
            <Link href={action.href} className="font-serif text-2xl leading-snug tracking-tight text-ink">
              {action.title}
            </Link>
            <p className="mt-1 text-base leading-relaxed text-ink">{action.body}</p>
          </li>
        ))}
      </ul>
    </div>
  );
}
