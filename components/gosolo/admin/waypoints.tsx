"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useState } from "react";
import { Choice, DeskIntro, TextField, deskButton } from "@/components/gosolo/admin/ui";
import { Button } from "@/components/ui/button";
import { WAYPOINTS } from "@/lib/catalog";
import { newWaypointId, resolveWaypoints } from "@/lib/admin/resolve";
import { COLOURS, note } from "@/lib/admin/state";
import { formatDate } from "@/lib/format";
import { useGoSolo } from "@/lib/gosolo";
import type { Waypoint, WaypointColour } from "@/lib/types";

const icons = ["🧭", "🪴", "🚶", "🌙", "🔥", "🌱"];

function blank(): Waypoint {
  return {
    id: "",
    slug: "",
    name: "",
    summary: "",
    description: "",
    focus: [],
    colour: "sage",
    icon: "🧭",
  };
}

export function WaypointsAdmin() {
  const { desk, library } = useGoSolo();
  const points = resolveWaypoints(desk, true);
  return (
    <>
      <DeskIntro eyebrow="Waypoints" title="Places along the way.">
        Create a waypoint, change its colour, or let one rest.
      </DeskIntro>
      <Button asChild className={`${deskButton} mt-8`}>
        <Link href="/admin/waypoints/new">Create waypoint</Link>
      </Button>
      <ul className="mt-8 space-y-4">
        {points.map((waypoint) => {
          const members = library.memberships.filter((item) => item.waypointId === waypoint.id).length;
          return (
            <li key={waypoint.id} className={`rounded-[28px] p-6 ${waypoint.colour === "clay" ? "bg-clay" : waypoint.colour === "gold" ? "bg-gold" : waypoint.colour === "mist" ? "bg-mist" : "bg-sage"}`}>
              <Link href={`/admin/waypoints/${waypoint.id}`} className="font-serif text-3xl text-ink">
                <span aria-hidden="true">{waypoint.icon ? `${waypoint.icon} ` : ""}</span>
                {waypoint.name}
              </Link>
              <p className="mt-2 text-ink-soft">
                {members} {members === 1 ? "member" : "members"}
                {desk.archivedWaypointIds.includes(waypoint.id) ? " · Archived" : ""}
                {desk.featuredWaypointIds.includes(waypoint.id) ? " · Featured" : ""}
              </p>
            </li>
          );
        })}
      </ul>
    </>
  );
}

export function WaypointEditor({ id }: { id?: string }) {
  const { desk, library, seeds, updateDesk } = useGoSolo();
  const router = useRouter();
  const existing = id ? resolveWaypoints(desk, true).find((item) => item.id === id) : undefined;
  const [waypoint, setWaypoint] = useState<Waypoint>(existing ?? blank());
  const members = library.memberships.filter((item) => item.waypointId === waypoint.id);
  const stories = library.stories.filter((story) => story.waypointId === waypoint.id).slice(0, 4);
  const campfire = library.campfire.filter((post) => post.waypointId === waypoint.id).slice(0, 4);
  const relatedSeeds = seeds.filter((seed) => seed.waypoints.includes(waypoint.id));

  function save(next: Waypoint) {
    updateDesk((admin) => {
      const catalog = WAYPOINTS.some((item) => item.id === next.id);
      const custom = admin.customWaypoints.some((item) => item.id === next.id);
      const state = catalog
        ? { ...admin, waypointEdits: { ...admin.waypointEdits, [next.id]: next } }
        : {
            ...admin,
            customWaypoints: custom
              ? admin.customWaypoints.map((item) => (item.id === next.id ? next : item))
              : [...admin.customWaypoints, next],
          };
      return note(state, `Saved waypoint “${next.name}”.`);
    });
  }

  return (
    <>
      <DeskIntro eyebrow="Waypoint" title={waypoint.name || "A new waypoint"}>
        {members.length} {members.length === 1 ? "member" : "members"}.
      </DeskIntro>
      <form
        className="mt-8 max-w-2xl space-y-5"
        onSubmit={(event) => {
          event.preventDefault();
          const withId = waypoint.id
            ? waypoint
            : { ...waypoint, id: newWaypointId(waypoint.name || "waypoint"), slug: newWaypointId(waypoint.name || "waypoint") };
          setWaypoint(withId);
          save(withId);
          if (!waypoint.id) router.replace(`/admin/waypoints/${withId.id}`);
        }}
      >
        <TextField label="Name" value={waypoint.name} onChange={(name) => setWaypoint({ ...waypoint, name })} />
        <TextField label="Short summary" value={waypoint.summary} onChange={(summary) => setWaypoint({ ...waypoint, summary })} />
        <TextField label="Description" value={waypoint.description} onChange={(description) => setWaypoint({ ...waypoint, description })} area />
        <TextField
          label="Focus, separated by commas"
          value={waypoint.focus.join(", ")}
          onChange={(value) => setWaypoint({ ...waypoint, focus: value.split(",").map((item) => item.trim()).filter(Boolean) })}
        />
        <Choice
          label="Colour"
          value={waypoint.colour ?? "sage"}
          options={COLOURS}
          onChange={(colour) => setWaypoint({ ...waypoint, colour: colour as WaypointColour })}
        />
        <fieldset>
          <legend className="text-sm">Icon</legend>
          <div className="mt-2 flex gap-2">
            {icons.map((icon) => (
              <button
                key={icon}
                type="button"
                aria-pressed={waypoint.icon === icon}
                className={`size-12 rounded-full text-xl ${waypoint.icon === icon ? "bg-ink" : "bg-white"}`}
                onClick={() => setWaypoint({ ...waypoint, icon })}
              >
                <span aria-hidden="true">{icon}</span>
                <span className="sr-only">{icon}</span>
              </button>
            ))}
          </div>
        </fieldset>
        <p className="text-sm text-ink-soft">Membership count: {members.length}</p>
        <div className="flex flex-wrap gap-2">
          <Button type="submit" className={deskButton}>Save waypoint</Button>
          {waypoint.id ? (
            <>
              <Button
                type="button"
                variant="outline"
                className={`${deskButton} bg-transparent`}
                onClick={() =>
                  updateDesk((admin) => {
                    const has = admin.featuredWaypointIds.includes(waypoint.id);
                    const featuredWaypointIds = has
                      ? admin.featuredWaypointIds.filter((item) => item !== waypoint.id)
                      : [...admin.featuredWaypointIds, waypoint.id];
                    return note({ ...admin, featuredWaypointIds }, `${has ? "Unfeatured" : "Featured"} ${waypoint.name}.`);
                  })
                }
              >
                {desk.featuredWaypointIds.includes(waypoint.id) ? "Unfeature" : "Feature"}
              </Button>
              <Button
                type="button"
                variant="outline"
                className={`${deskButton} bg-transparent`}
                onClick={() =>
                  updateDesk((admin) => {
                    const has = admin.archivedWaypointIds.includes(waypoint.id);
                    const archivedWaypointIds = has
                      ? admin.archivedWaypointIds.filter((item) => item !== waypoint.id)
                      : [...admin.archivedWaypointIds, waypoint.id];
                    return note({ ...admin, archivedWaypointIds }, `${has ? "Restored" : "Archived"} ${waypoint.name}.`);
                  })
                }
              >
                {desk.archivedWaypointIds.includes(waypoint.id) ? "Restore" : "Archive"}
              </Button>
            </>
          ) : null}
        </div>
      </form>
      {waypoint.id ? (
        <div className="mt-12 grid gap-8 lg:grid-cols-2">
          <section>
            <h2 className="font-serif text-3xl">Members</h2>
            <ul className="mt-3 space-y-2">
              {members.map((item) => {
                const person = library.profiles.find((profile) => profile.id === item.userId);
                return <li key={item.userId}>{person?.displayName ?? "Someone"} · {formatDate(item.joinedAt)}</li>;
              })}
            </ul>
          </section>
          <section>
            <h2 className="font-serif text-3xl">Associated seeds</h2>
            <ul className="mt-3 space-y-2">
              {relatedSeeds.map((seed) => (
                <li key={seed.id}>{seed.title}</li>
              ))}
            </ul>
          </section>
          <section>
            <h2 className="font-serif text-3xl">Recent Out There stories</h2>
            <ul className="mt-3 space-y-2">
              {stories.length === 0 ? <li className="text-ink-soft">None yet.</li> : null}
              {stories.map((story) => (
                <li key={story.id}>{story.title}</li>
              ))}
            </ul>
          </section>
          <section>
            <h2 className="font-serif text-3xl">Campfire activity</h2>
            <ul className="mt-3 space-y-2">
              {campfire.length === 0 ? <li className="text-ink-soft">Quiet here.</li> : null}
              {campfire.map((post) => (
                <li key={post.id}>{post.title}</li>
              ))}
            </ul>
          </section>
        </div>
      ) : null}
    </>
  );
}
