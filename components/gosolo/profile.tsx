"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { Frame, PageIntro, Panel, PersonAvatar } from "@/components/gosolo/pieces";
import { NextStep } from "@/components/gosolo/next-step";
import { getSeed, getWaypoint } from "@/lib/catalog";
import { formatMonthYear, formatRelative, interestLabel } from "@/lib/format";
import { useGoSolo } from "@/lib/gosolo";
import { INTENTIONS } from "@/lib/types";

export function ProfileView({ profileId }: { profileId?: string }) {
  const { user, world } = useGoSolo();
  const router = useRouter();
  const id = profileId || user?.id;
  const profile = world.profiles.find((item) => item.id === id);
  if (!profile) {
    return (
      <Frame>
        <PageIntro title="This person is not in the room." />
      </Frame>
    );
  }
  const isSelf = profile.id === user?.id;
  const showLocation = isSelf || profile.showLocation;
  const showWaypoints = isSelf || profile.showWaypoints;
  const waypoints = world.memberships
    .filter((item) => item.userId === profile.id)
    .map((item) => getWaypoint(item.waypointId))
    .filter(Boolean);
  const seeds = world.userSeeds.filter((item) => item.userId === profile.id && item.status === "active");
  const stories = world.stories.filter((item) => item.authorId === profile.id).slice(0, 4);
  const offers = world.offers.filter((item) => item.userId === profile.id);
  const requests = world.requests.filter((item) => item.userId === profile.id);
  const connections = world.connections.filter(
    (item) => item.fromUserId === profile.id || item.toUserId === profile.id,
  );

  return (
    <Frame>
      <div className="flex flex-col gap-6 sm:flex-row sm:items-end">
        <PersonAvatar profile={profile} className="size-24 text-3xl" />
        <div>
          <p className="text-sm text-ink-soft">Here since {formatMonthYear(profile.createdAt)}</p>
          <h1 className="mt-2 font-serif text-5xl tracking-tight sm:text-6xl">{profile.displayName || "Unnamed"}</h1>
          {showLocation && profile.location ? <p className="mt-3 text-lg text-ink-soft">{profile.location}</p> : null}
        </div>
      </div>
      {profile.bio ? <p className="mt-8 max-w-2xl text-xl leading-relaxed">{profile.bio}</p> : null}
      {isSelf && profile.intentions.length > 0 ? (
        <p className="mt-6 text-ink-soft">
          Here for{" "}
          {profile.intentions
            .map((item) => INTENTIONS.find((intention) => intention.id === item)?.label.toLowerCase())
            .filter(Boolean)
            .join(", ")}
          .
        </p>
      ) : null}
      {isSelf ? (
        <button
          type="button"
          className="mt-6 text-sm underline decoration-ink/20 underline-offset-4"
          onClick={() => router.push("/settings")}
        >
          Edit in settings
        </button>
      ) : null}

      <section className="mt-12">
        <h2 className="font-serif text-3xl">Interests</h2>
        {profile.interests.length === 0 ? (
          <p className="mt-3 text-ink-soft">None named yet.</p>
        ) : (
          <ul className="mt-4 flex flex-wrap gap-2">
            {profile.interests.map((interest) => (
              <li key={interest} className="rounded-full bg-sage px-4 py-2">
                {interestLabel(interest)}
              </li>
            ))}
          </ul>
        )}
      </section>

      {showWaypoints ? (
        <section className="mt-12">
          <h2 className="font-serif text-3xl">Waypoints</h2>
          <ul className="mt-4 space-y-2">
            {waypoints.map((waypoint) =>
              waypoint ? (
                <li key={waypoint.id}>
                  <Link href={`/waypoints/${waypoint.slug}`} className="text-lg underline decoration-ink/15 underline-offset-4">
                    {waypoint.name}
                  </Link>
                </li>
              ) : null,
            )}
          </ul>
        </section>
      ) : null}

      <section className="mt-12">
        <h2 className="font-serif text-3xl">Current seeds</h2>
        {seeds.length === 0 ? (
          <p className="mt-3 text-ink-soft">Nothing active right now.</p>
        ) : (
          <ul className="mt-4 space-y-3">
            {seeds.map((item) => {
              const seed = getSeed(item.seedId);
              return seed ? (
                <li key={item.id}>
                  <Link href={`/seeds/${seed.id}`} className="text-lg underline decoration-ink/15 underline-offset-4">
                    {seed.title}
                  </Link>
                </li>
              ) : null;
            })}
          </ul>
        )}
      </section>

      <section className="mt-12">
        <h2 className="font-serif text-3xl">Recent Out There stories</h2>
        {stories.length === 0 ? (
          <p className="mt-3 text-ink-soft">No stories brought back yet.</p>
        ) : (
          <ul className="mt-4 space-y-4">
            {stories.map((story) => (
              <li key={story.id}>
                <Link href={`/out-there/${story.id}`} className="font-serif text-2xl">
                  {story.title}
                </Link>
                <p className="text-sm text-ink-soft">{formatRelative(story.createdAt)}</p>
              </li>
            ))}
          </ul>
        )}
      </section>

      <section className="mt-12">
        <h2 className="font-serif text-3xl">Skill swap</h2>
        {offers.length + requests.length + connections.length === 0 ? (
          <p className="mt-3 text-ink-soft">No exchanges yet.</p>
        ) : (
          <div className="mt-4 grid gap-4 md:grid-cols-2">
            {offers.map((offer) => (
              <Panel key={offer.id} tone="gold">
                <p className="text-sm text-ink-soft">Offering</p>
                <h3 className="mt-2 text-xl">{offer.skill}</h3>
                <p className="mt-2 text-ink-soft">{offer.description}</p>
              </Panel>
            ))}
            {requests.map((request) => (
              <Panel key={request.id} tone="mist">
                <p className="text-sm text-ink-soft">Asking</p>
                <h3 className="mt-2 text-xl">{request.skill}</h3>
                <p className="mt-2 text-ink-soft">{request.description}</p>
              </Panel>
            ))}
            {connections.map((connection) => (
              <Panel key={connection.id}>
                <p className="text-sm text-ink-soft">A connection</p>
                <p className="mt-2">{connection.note}</p>
              </Panel>
            ))}
          </div>
        )}
      </section>
      {isSelf ? <NextStep /> : null}
    </Frame>
  );
}
