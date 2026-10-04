"use client";

import Link from "next/link";
import { AuthorLine, Frame, Panel, TextLink } from "@/components/gosolo/pieces";
import { NextStep } from "@/components/gosolo/next-step";
import { getSeed, getWaypoint } from "@/lib/catalog";
import { formatRelative } from "@/lib/format";
import { useGoSolo } from "@/lib/gosolo";

export function Dashboard() {
  const { user, world } = useGoSolo();
  if (!user) return null;
  const names = new Map(world.profiles.map((profile) => [profile.id, profile]));
  const active = world.userSeeds
    .filter((seed) => seed.userId === user.id && seed.status === "active")
    .sort((a, b) => (a.startedAt < b.startedAt ? 1 : -1));
  const current = active[0];
  const currentSeed = current ? getSeed(current.seedId) : undefined;
  const stories = world.stories.slice(0, 2);
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
      <div className="mt-10">
        <NextStep compact />
      </div>
      <div className="mt-6 grid gap-4 lg:grid-cols-2">
        <Panel tone="sage">
          <h2 className="font-serif text-3xl text-ink">Current seed</h2>
          {currentSeed ? (
            <div className="mt-4">
              <p className="text-lg leading-relaxed">{currentSeed.title}</p>
              <p className="mt-2 text-ink-soft">{currentSeed.prompt}</p>
              <div className="mt-6">
                <TextLink href={`/seeds/${currentSeed.id}`}>Stay with this seed</TextLink>
              </div>
            </div>
          ) : (
            <div className="mt-4">
              <p className="text-lg leading-relaxed text-ink">Nothing is planted yet. That is a fine place to start.</p>
              <div className="mt-6">
                <TextLink href="/seeds">Find a seed</TextLink>
              </div>
            </div>
          )}
        </Panel>
        <Panel tone="mist">
          <h2 className="font-serif text-3xl text-ink">My waypoints</h2>
          {mine.length > 0 ? (
            <ul className="mt-4 space-y-3">
              {mine.map((item) => {
                const waypoint = getWaypoint(item.waypointId);
                if (!waypoint) return null;
                return (
                  <li key={item.waypointId}>
                    <Link href={`/waypoints/${waypoint.slug}`} className="text-lg underline decoration-ink/15 underline-offset-4">
                      {waypoint.name}
                    </Link>
                  </li>
                );
              })}
            </ul>
          ) : (
            <p className="mt-4 text-lg leading-relaxed">
              You have not chosen a waypoint yet.{" "}
              <TextLink href="/waypoints">See the three</TextLink>
            </p>
          )}
        </Panel>
      </div>
      <Panel className="mt-4">
        <div className="flex items-end justify-between gap-4">
          <h2 className="font-serif text-3xl text-ink">Latest from Out There</h2>
          <TextLink href="/out-there">All stories</TextLink>
        </div>
        <ul className="mt-6 space-y-6">
          {stories.length === 0 ? (
            <li className="text-lg text-ink-soft">No stories yet. The world is still large.</li>
          ) : (
            stories.map((story) => (
              <li key={story.id} className="border-t border-ink/5 pt-6 first:border-0 first:pt-0">
                <AuthorLine
                  profile={names.get(story.authorId)}
                  meta={formatRelative(story.createdAt)}
                  href={`/profile/${story.authorId}`}
                />
                <Link href={`/out-there/${story.id}`} className="mt-4 block font-serif text-3xl leading-tight text-ink">
                  {story.title}
                </Link>
              </li>
            ))
          )}
        </ul>
      </Panel>
      <Panel tone="clay" className="mt-4">
        <div className="flex items-end justify-between gap-4">
          <h2 className="font-serif text-3xl text-ink">Campfire</h2>
          <TextLink href="/campfire">Pull up a chair</TextLink>
        </div>
        <ul className="mt-6 space-y-5">
          {fires.length === 0 ? (
            <li className="text-lg">The chairs are here. The first thing said can be ordinary.</li>
          ) : (
            fires.map((post) => (
              <li key={post.id}>
                <Link href={`/campfire/${post.id}`} className="font-serif text-2xl leading-tight text-ink">
                  {post.title}
                </Link>
                <p className="mt-2 line-clamp-2 text-ink">{post.body}</p>
              </li>
            ))
          )}
        </ul>
      </Panel>
      <div className="mt-16 max-w-xl">
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
