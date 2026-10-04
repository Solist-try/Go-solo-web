"use client";

import Link from "next/link";
import { AuthorLine, Frame, PageIntro, Panel, PrimaryLink, pill } from "@/components/gosolo/pieces";
import { NextStep } from "@/components/gosolo/next-step";
import { Button } from "@/components/ui/button";
import { getWaypoint } from "@/lib/catalog";
import { formatRelative } from "@/lib/format";
import { useGoSolo } from "@/lib/gosolo";

export function WaypointIndex() {
  const { waypoints, user } = useGoSolo();
  return (
    <Frame>
      <PageIntro title="Waypoints">
        Places where people exploring similar parts of life gather, share experiences, and continue
        their journey. Joining and leaving is simple.
      </PageIntro>
      <ul className="mt-10 grid gap-4 lg:grid-cols-3">
        {waypoints.map((waypoint) => (
          <li key={waypoint.id}>
            <Link href={`/waypoints/${waypoint.slug}`} className="block h-full rounded-[28px] bg-white/80 p-7 shadow-soft">
              <h2 className="font-serif text-3xl leading-tight tracking-tight">{waypoint.name}</h2>
              <p className="mt-4 leading-relaxed text-ink-soft">{waypoint.description}</p>
              <p className="mt-6 text-sm">{waypoint.focus.join(" · ")}</p>
            </Link>
          </li>
        ))}
      </ul>
      {user ? <NextStep /> : (
        <div className="mt-10">
          <PrimaryLink href="/register">Join to choose a waypoint</PrimaryLink>
        </div>
      )}
    </Frame>
  );
}

export function WaypointDetail({ slug }: { slug: string }) {
  const waypoint = getWaypoint(slug);
  const { user, world, joinWaypoint, leaveWaypoint, seeds } = useGoSolo();
  if (!waypoint) {
    return (
      <Frame>
        <PageIntro title="This waypoint is not on the map." />
      </Frame>
    );
  }
  const joined = world.memberships.some(
    (item) => item.userId === user?.id && item.waypointId === waypoint.id,
  );
  const members = world.memberships
    .filter((item) => item.waypointId === waypoint.id)
    .map((item) => world.profiles.find((profile) => profile.id === item.userId))
    .filter((profile) => profile && (profile.showWaypoints || profile.id === user?.id));
  const conversations = world.campfire.filter((post) => post.waypointId === waypoint.id).slice(0, 3);
  const stories = world.stories.filter((story) => story.waypointId === waypoint.id).slice(0, 3);
  const related = seeds.filter((seed) => seed.waypoints.includes(waypoint.id)).slice(0, 4);
  const names = new Map(world.profiles.map((profile) => [profile.id, profile]));

  return (
    <Frame>
      <PageIntro eyebrow="Waypoint" title={waypoint.name}>
        {waypoint.description}
      </PageIntro>
      <ul className="mt-6 flex flex-wrap gap-2">
        {waypoint.focus.map((item) => (
          <li key={item} className="rounded-full bg-sage px-4 py-2 text-sm">
            {item}
          </li>
        ))}
      </ul>
      <div className="mt-8">
        {!user ? (
          <PrimaryLink href={`/register?next=${encodeURIComponent(`/waypoints/${waypoint.slug}`)}`}>
            Join Go Solo to walk here
          </PrimaryLink>
        ) : joined ? (
          <Button variant="outline" className={`${pill} bg-transparent`} onClick={() => void leaveWaypoint(waypoint.id)}>
            Leave quietly
          </Button>
        ) : (
          <Button className={pill} onClick={() => void joinWaypoint(waypoint.id)}>
            Join this waypoint
          </Button>
        )}
        {joined ? <p className="mt-3 text-sm text-ink-soft">You can come back whenever you want.</p> : null}
      </div>

      <section className="mt-14">
        <h2 className="font-serif text-4xl tracking-tight">Overview</h2>
        <p className="mt-4 max-w-2xl text-lg leading-relaxed text-ink-soft">{waypoint.summary}</p>
      </section>

      <section className="mt-12">
        <h2 className="font-serif text-4xl tracking-tight">Campfire conversations</h2>
        {conversations.length === 0 ? (
          <p className="mt-4 text-lg text-ink-soft">No one has pulled up a chair here yet.</p>
        ) : (
          <ul className="mt-6 space-y-4">
            {conversations.map((post) => (
              <li key={post.id}>
                <Link href={`/campfire/${post.id}`} className="block rounded-[28px] bg-gold/80 p-6">
                  <span className="font-serif text-3xl leading-tight">{post.title}</span>
                  <span className="mt-2 block text-ink-soft">{post.body}</span>
                </Link>
              </li>
            ))}
          </ul>
        )}
      </section>

      <section className="mt-12">
        <h2 className="font-serif text-4xl tracking-tight">Related seeds</h2>
        <ul className="mt-6 grid gap-4 md:grid-cols-2">
          {related.map((seed) => (
            <li key={seed.id}>
              <Link href={`/seeds/${seed.id}`} className="block rounded-[28px] bg-white/80 p-6 shadow-soft">
                <span className="font-serif text-2xl">{seed.title}</span>
                <span className="mt-2 block text-ink-soft">{seed.description}</span>
              </Link>
            </li>
          ))}
        </ul>
      </section>

      <section className="mt-12">
        <h2 className="font-serif text-4xl tracking-tight">Recent Out There stories</h2>
        {stories.length === 0 ? (
          <p className="mt-4 text-lg text-ink-soft">Stories from this stretch will gather here.</p>
        ) : (
          <ul className="mt-6 space-y-4">
            {stories.map((story) => (
              <li key={story.id} className="rounded-[28px] bg-clay/70 p-6">
                <AuthorLine
                  profile={names.get(story.authorId)}
                  meta={formatRelative(story.createdAt)}
                  href={user ? `/profile/${story.authorId}` : undefined}
                />
                <Link href={user ? `/out-there/${story.id}` : "/login"} className="mt-4 block font-serif text-3xl">
                  {story.title}
                </Link>
              </li>
            ))}
          </ul>
        )}
      </section>

      <section className="mt-12">
        <h2 className="font-serif text-4xl tracking-tight">People walking this stretch</h2>
        {members.length === 0 ? (
          <p className="mt-4 text-lg text-ink-soft">The path is open.</p>
        ) : (
          <ul className="mt-6 grid gap-4 sm:grid-cols-2">
            {members.map((profile) =>
              profile ? (
                <li key={profile.id}>
                  <Panel>
                    <AuthorLine
                      profile={profile}
                      meta={profile.showLocation || profile.id === user?.id ? profile.location : undefined}
                      href={user ? `/profile/${profile.id}` : undefined}
                    />
                    {profile.bio ? <p className="mt-4 text-ink-soft">{profile.bio}</p> : null}
                  </Panel>
                </li>
              ) : null,
            )}
          </ul>
        )}
      </section>
      {user ? <NextStep /> : null}
    </Frame>
  );
}
