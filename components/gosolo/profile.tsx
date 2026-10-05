"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { Frame, PageIntro, PersonAvatar } from "@/components/gosolo/pieces";
import { formatMonthYear, formatRelative } from "@/lib/format";
import { frequencyLabel, gardenOf, styleLabel, supportLabel, uniqueNames } from "@/lib/garden";
import { useCatalog, useGoSolo } from "@/lib/gosolo";
import type { CampfirePost, OutTherePost, Profile } from "@/lib/types";

export function ProfileView({ profileId }: { profileId?: string }) {
  const { user, world } = useGoSolo();
  const { getWaypoint } = useCatalog();
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
  const garden = gardenOf(profile);
  const memberships = world.memberships.filter((item) => item.userId === profile.id);
  const waypoints = memberships
    .map((item) => ({ membership: item, waypoint: getWaypoint(item.waypointId) }))
    .filter((item) => item.waypoint);
  const offers = world.offers.filter((item) => item.userId === profile.id);
  const requests = world.requests.filter((item) => item.userId === profile.id);
  const helpGrowing = uniqueNames([...garden.helpGrowing, ...requests.map((item) => item.skill)]);
  const helpPlant = uniqueNames([...garden.helpPlant, ...offers.map((item) => item.skill)]);
  const stories = world.stories
    .filter((item) => item.authorId === profile.id && !item.hidden && !item.example)
    .slice(0, 3);

  return (
    <Frame>
      <header className="flex flex-col gap-6 sm:flex-row sm:items-start">
        <PersonAvatar profile={profile} className="size-28 text-3xl" />
        <div className="max-w-2xl">
          <h1 className="font-serif text-5xl tracking-tight text-ink sm:text-6xl">{profile.displayName || "Unnamed"}</h1>
          {showLocation && profile.location ? <p className="mt-3 text-lg text-ink">{profile.location}</p> : null}
          {profile.bio ? <p className="mt-4 text-xl leading-relaxed text-ink">{profile.bio}</p> : null}
          <p className="mt-4 text-sm text-ink-soft">Joined {formatMonthYear(profile.createdAt)}</p>
          {showWaypoints && waypoints.length > 0 ? (
            <p className="mt-3 text-ink">
              <span className="text-ink-soft">Waypoints · </span>
              {waypoints.map((item, index) => (
                <span key={item.waypoint!.id}>
                  {index > 0 ? ", " : null}
                  <Link href={`/waypoints/${item.waypoint!.slug}`} className="underline decoration-ink/20 underline-offset-4">
                    {item.waypoint!.name}
                  </Link>
                </span>
              ))}
            </p>
          ) : null}
          {isSelf ? (
            <button
              type="button"
              className="mt-5 text-sm underline decoration-ink/20 underline-offset-4"
              onClick={() => router.push("/settings#garden")}
            >
              Tend your garden
            </button>
          ) : null}
        </div>
      </header>

      <section className="mt-16" aria-labelledby="growing-title">
        <h2 id="growing-title" className="font-serif text-4xl tracking-tight text-ink">
          Seeds I&apos;m Growing
        </h2>
        {garden.growing.length === 0 ? (
          <p className="mt-4 text-ink-soft">{isSelf ? "Name a seed when you are ready." : "No seeds named yet."}</p>
        ) : (
          <ul className="mt-6 grid gap-4 md:grid-cols-2">
            {garden.growing.map((seed) => (
              <li key={seed.id} className="rounded-[28px] bg-sage/80 p-6">
                <h3 className="font-serif text-3xl tracking-tight text-ink">{seed.name}</h3>
                <dl className="mt-4 space-y-2 text-ink">
                  <div>
                    <dt className="text-sm text-ink-soft">Status</dt>
                    <dd>{seed.status === "resting" ? "Resting" : "Active"}</dd>
                  </div>
                  <div>
                    <dt className="text-sm text-ink-soft">Active since</dt>
                    <dd>{formatMonthYear(seed.since)}</dd>
                  </div>
                  <div>
                    <dt className="text-sm text-ink-soft">Looking for support?</dt>
                    <dd>{seed.lookingForSupport ? "Yes" : "No"}</dd>
                  </div>
                </dl>
              </li>
            ))}
          </ul>
        )}
      </section>

      <NameList
        id="help-growing-title"
        title="Seeds I'd Like Help Growing"
        intro="The futures where another person would make the tending easier."
        items={helpGrowing}
        empty={isSelf ? "Name what you would like help with." : "Nothing named yet."}
        tone="bg-mist"
      />

      <section className="mt-16" aria-labelledby="help-plant-title">
        <h2 id="help-plant-title" className="font-serif text-4xl tracking-tight text-ink">
          Seeds I&apos;m Happy to Help Plant
        </h2>
        <p className="mt-4 max-w-2xl text-lg leading-relaxed text-ink">
          What knowledge, experience or encouragement are you happy to share?
        </p>
        {garden.helpPlantNote ? <p className="mt-4 max-w-2xl text-lg leading-relaxed text-ink">{garden.helpPlantNote}</p> : null}
        {helpPlant.length === 0 ? (
          <p className="mt-4 text-ink-soft">{isSelf ? "Add something you can share." : "Nothing named yet."}</p>
        ) : (
          <ul className="mt-6 flex flex-wrap gap-3">
            {helpPlant.map((item) => (
              <li key={item} className="rounded-[24px] bg-gold px-5 py-3 text-lg text-ink">
                {item}
              </li>
            ))}
          </ul>
        )}
      </section>

      <NameList
        id="support-title"
        title="I'd Appreciate Support With"
        intro="This is what helps SAME find a fitting partner."
        items={garden.supportWith.map(supportLabel)}
        empty={isSelf ? "Choose the kinds of support you want." : "Not named yet."}
        tone="bg-white/80"
      />
      {garden.sameNotes ? <p className="mt-4 max-w-2xl text-lg leading-relaxed text-ink">{garden.sameNotes}</p> : null}

      <section className="mt-16 max-w-2xl" aria-labelledby="frequency-title">
        <h2 id="frequency-title" className="font-serif text-4xl tracking-tight text-ink">
          Preferred Check-in Frequency
        </h2>
        <p className="mt-4 text-lg text-ink">
          {frequencyLabel(garden.checkInFrequency) || (isSelf ? "Choose a rhythm in settings." : "Not chosen yet.")}
        </p>
      </section>

      <section className="mt-16 max-w-2xl" aria-labelledby="style-title">
        <h2 id="style-title" className="font-serif text-4xl tracking-tight text-ink">
          Preferred Check-in Style
        </h2>
        <p className="mt-4 text-lg text-ink">
          {styleLabel(garden.checkInStyle) || (isSelf ? "Choose a way to check in." : "Not chosen yet.")}
        </p>
      </section>

      <section className="mt-16" aria-labelledby="stories-title">
        <h2 id="stories-title" className="font-serif text-4xl tracking-tight text-ink">
          Recent Out There Stories
        </h2>
        {stories.length === 0 ? (
          <p className="mt-4 text-ink-soft">No experiences brought back yet.</p>
        ) : (
          <ul className="mt-6 space-y-6">
            {stories.map((story) => (
              <li key={story.id}>
                <Link href={`/out-there/${story.id}`} className="font-serif text-3xl tracking-tight text-ink">
                  {story.title}
                </Link>
                <p className="mt-2 max-w-2xl leading-relaxed text-ink">{story.whatDidYouDo}</p>
                <p className="mt-2 text-sm text-ink-soft">{formatRelative(story.createdAt)}</p>
              </li>
            ))}
          </ul>
        )}
      </section>

      {showWaypoints ? (
        <section className="mt-16" aria-labelledby="waypoints-title">
          <h2 id="waypoints-title" className="font-serif text-4xl tracking-tight text-ink">
            Waypoints
          </h2>
          {waypoints.length === 0 ? (
            <p className="mt-4 text-ink-soft">No waypoint yet.</p>
          ) : (
            <ul className="mt-6 grid gap-4 lg:grid-cols-2">
              {waypoints.map(({ membership, waypoint }) =>
                waypoint ? (
                  <WaypointCard
                    key={waypoint.id}
                    name={waypoint.name}
                    href={`/waypoints/${waypoint.slug}`}
                    since={membership.joinedAt}
                    activity={latestActivity(profile, waypoint.id, world.stories, world.campfire)}
                    conversations={recentConversations(waypoint.id, world.campfire, world.profiles)}
                  />
                ) : null,
              )}
            </ul>
          )}
        </section>
      ) : null}
    </Frame>
  );
}

function NameList({
  id,
  title,
  intro,
  items,
  empty,
  tone,
}: {
  id: string;
  title: string;
  intro?: string;
  items: string[];
  empty: string;
  tone: string;
}) {
  return (
    <section className="mt-16" aria-labelledby={id}>
      <h2 id={id} className="font-serif text-4xl tracking-tight text-ink">
        {title}
      </h2>
      {intro ? <p className="mt-4 max-w-2xl text-lg leading-relaxed text-ink">{intro}</p> : null}
      {items.length === 0 ? (
        <p className="mt-4 text-ink-soft">{empty}</p>
      ) : (
        <ul className="mt-6 flex flex-wrap gap-3">
          {items.map((item) => (
            <li key={item} className={`rounded-[24px] px-5 py-3 text-lg text-ink ${tone}`}>
              {item}
            </li>
          ))}
        </ul>
      )}
    </section>
  );
}

function WaypointCard({
  name,
  href,
  since,
  activity,
  conversations,
}: {
  name: string;
  href: string;
  since: string;
  activity: { title: string; href: string } | null;
  conversations: { id: string; title: string; href: string; who: string; at: string }[];
}) {
  return (
    <li className="rounded-[28px] bg-white/80 p-6 shadow-soft">
      <h3 className="font-serif text-3xl tracking-tight text-ink">
        <Link href={href} className="underline decoration-ink/20 underline-offset-4">
          {name}
        </Link>
      </h3>
      <p className="mt-3 text-sm text-ink-soft">Member since {formatMonthYear(since)}</p>
      <p className="mt-5 text-sm text-ink-soft">Current activity</p>
      {activity ? (
        <Link href={activity.href} className="mt-1 block text-lg text-ink">
          {activity.title}
        </Link>
      ) : (
        <p className="mt-1 text-ink">Quiet for now.</p>
      )}
      <p className="mt-5 text-sm text-ink-soft">Recent conversations</p>
      {conversations.length === 0 ? (
        <p className="mt-1 text-ink">The room is quiet.</p>
      ) : (
        <ul className="mt-2 space-y-3">
          {conversations.map((item) => (
            <li key={item.id}>
              <Link href={item.href} className="text-lg text-ink">
                {item.title}
              </Link>
              <p className="text-sm text-ink-soft">
                {item.who} · {formatRelative(item.at)}
              </p>
            </li>
          ))}
        </ul>
      )}
    </li>
  );
}

function latestActivity(
  profile: Profile,
  waypointId: string,
  stories: OutTherePost[],
  campfire: CampfirePost[],
) {
  const items = [
    ...stories
      .filter((item) => item.authorId === profile.id && item.waypointId === waypointId && !item.hidden && !item.example)
      .map((item) => ({ at: item.createdAt, title: item.title, href: `/out-there/${item.id}` })),
    ...campfire
      .filter((item) => item.authorId === profile.id && item.waypointId === waypointId && !item.hidden)
      .map((item) => ({ at: item.createdAt, title: item.title, href: `/campfire/${item.id}` })),
  ].sort((a, b) => b.at.localeCompare(a.at));
  return items[0] ?? null;
}

function recentConversations(waypointId: string, campfire: CampfirePost[], profiles: Profile[]) {
  const names = new Map(profiles.map((profile) => [profile.id, profile.displayName]));
  return campfire
    .filter((item) => item.waypointId === waypointId && !item.hidden)
    .slice(0, 2)
    .map((item) => ({
      id: item.id,
      title: item.title,
      href: `/campfire/${item.id}`,
      who: names.get(item.authorId) || "A member",
      at: item.createdAt,
    }));
}
