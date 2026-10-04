"use client";

import Link from "next/link";
import { useMemo, useState } from "react";
import { DeskIntro, QuietTable, downloadJson, memberEmail } from "@/components/gosolo/admin/ui";
import { areaClass, fieldClass, pill } from "@/components/gosolo/pieces";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { lastActiveAt, memberDossier } from "@/lib/admin/insights";
import { note } from "@/lib/admin/state";
import { formatDate, interestLabel } from "@/lib/format";
import { useGoSolo } from "@/lib/gosolo";
import { INTENTIONS, type MemberStatus, type Profile } from "@/lib/types";

function statusWord(status?: string) {
  if (status === "suspended") return "Paused";
  if (status === "deactivated") return "Resting";
  return "Active";
}

export function MembersPage() {
  const { library, seeds, user } = useGoSolo();
  const [query, setQuery] = useState("");
  const rows = useMemo(() => {
    const needle = query.trim().toLowerCase();
    return library.profiles
      .filter((profile) => profile.id !== user?.id)
      .map((profile) => ({
        profile,
        last: lastActiveAt(profile, library),
        waypoints: library.memberships.filter((item) => item.userId === profile.id).length,
        seeds: library.userSeeds.filter((item) => item.userId === profile.id && item.status === "active").length,
        stories: library.stories.filter((item) => item.authorId === profile.id).length,
        campfire: library.campfire.filter((item) => item.authorId === profile.id).length,
      }))
      .filter((row) => {
        if (!needle) return true;
        return (
          row.profile.displayName.toLowerCase().includes(needle) ||
          memberEmail(row.profile).toLowerCase().includes(needle)
        );
      })
      .sort((a, b) => (a.last < b.last ? 1 : -1));
  }, [library, query, user?.id]);

  return (
    <>
      <DeskIntro eyebrow="Members" title="The people in the room.">
        A table for care, not surveillance. Open someone when you need the story of how they arrived.
      </DeskIntro>
      <div className="mt-8 flex flex-wrap items-center gap-3">
        <Input
          className={`${fieldClass} max-w-sm`}
          value={query}
          onChange={(event) => setQuery(event.target.value)}
          placeholder="Search by name or email"
          aria-label="Search members"
        />
        <Button
          type="button"
          variant="outline"
          className={`${pill} bg-transparent`}
          onClick={() =>
            downloadJson(
              "gosolo-members.json",
              rows.map((row) => ({
                name: row.profile.displayName,
                email: memberEmail(row.profile),
                joined: row.profile.createdAt,
                lastActive: row.last,
                status: row.profile.status ?? "active",
                waypoints: row.waypoints,
                activeSeeds: row.seeds,
                outThere: row.stories,
                campfire: row.campfire,
              })),
            )
          }
        >
          Export data
        </Button>
      </div>
      <QuietTable>
        <thead className="text-sm text-ink-soft">
          <tr>
            {["Name", "Email", "Joined", "Last active", "Waypoints", "Active seeds", "Out There", "Campfire"].map((heading) => (
              <th key={heading} className="px-3 py-3 font-normal">
                {heading}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {rows.map((row) => (
            <tr key={row.profile.id} className="border-t border-ink/10">
              <td className="px-3 py-4">
                <Link href={`/admin/members/${row.profile.id}`} className="text-lg text-ink underline decoration-ink/15 underline-offset-4">
                  {row.profile.displayName || "Unnamed"}
                </Link>
                <span className="mt-1 block text-sm text-ink-soft">{statusWord(row.profile.status)}</span>
              </td>
              <td className="px-3 py-4 text-ink-soft">{memberEmail(row.profile)}</td>
              <td className="px-3 py-4">{formatDate(row.profile.createdAt)}</td>
              <td className="px-3 py-4">{formatDate(row.last)}</td>
              <td className="px-3 py-4">{row.waypoints}</td>
              <td className="px-3 py-4">{row.seeds}</td>
              <td className="px-3 py-4">{row.stories}</td>
              <td className="px-3 py-4">{row.campfire}</td>
            </tr>
          ))}
        </tbody>
      </QuietTable>
      <p className="mt-4 text-sm text-ink-soft">{seeds.length} seeds exist in the room. Active seeds above are the ones still being lived.</p>
    </>
  );
}

export function MemberDetail({ id }: { id: string }) {
  const { library, seeds, waypoints, desk, updateDesk, notifyMember, user } = useGoSolo();
  const profile = library.profiles.find((item) => item.id === id);
  const [editing, setEditing] = useState(false);
  const [confirming, setConfirming] = useState(false);
  const [draft, setDraft] = useState<Profile | null>(profile ?? null);

  if (!profile || !draft) {
    return <DeskIntro title="This person is no longer in the room." />;
  }

  const dossier = memberDossier(profile, library, seeds, waypoints);
  const warnings = desk.warnings.filter((item) => item.userId === profile.id);
  const self = profile.id === user?.id;

  function save() {
    if (!draft) return;
    updateDesk((admin) =>
      note(
        {
          ...admin,
          memberEdits: {
            ...admin.memberEdits,
            [profile!.id]: {
              displayName: draft.displayName,
              bio: draft.bio,
              location: draft.location,
              intentions: draft.intentions,
              interests: draft.interests,
            },
          },
        },
        `Updated ${draft.displayName || "a member"}.`,
      ),
    );
    setEditing(false);
  }

  function setStatus(status: MemberStatus) {
    updateDesk((admin) =>
      note(
        { ...admin, memberStatus: { ...admin.memberStatus, [profile!.id]: status } },
        status === "active"
          ? `Reopened ${profile!.displayName}.`
          : status === "suspended"
            ? `Paused ${profile!.displayName}.`
            : `Set ${profile!.displayName} to resting.`,
      ),
    );
  }

  return (
    <>
      <DeskIntro eyebrow="Member" title={profile.displayName || "Unnamed"}>
        {statusWord(profile.status)} · Joined {formatDate(profile.createdAt)} · Last active {formatDate(dossier.lastActive)}
      </DeskIntro>
      <div className="mt-6 flex flex-wrap gap-2">
        <Button type="button" variant="outline" className={`${pill} bg-transparent`} onClick={() => setEditing((value) => !value)}>
          {editing ? "Close edit" : "Edit"}
        </Button>
        <Button type="button" variant="outline" className={`${pill} bg-transparent`} disabled={self} onClick={() => setStatus("deactivated")}>
          Deactivate
        </Button>
        <Button type="button" variant="outline" className={`${pill} bg-transparent`} disabled={self} onClick={() => setStatus("suspended")}>
          Suspend
        </Button>
        <Button type="button" variant="outline" className={`${pill} bg-transparent`} disabled={self} onClick={() => setStatus("active")}>
          Restore
        </Button>
        <Button
          type="button"
          variant="outline"
          className={`${pill} bg-transparent`}
          onClick={() => downloadJson(`${profile.id}.json`, dossier)}
        >
          Export data
        </Button>
        <Button type="button" variant="outline" className={`${pill} bg-transparent`} disabled={self} onClick={() => setConfirming(true)}>
          Delete
        </Button>
      </div>
      {confirming ? (
        <div className="mt-4 max-w-xl rounded-[28px] bg-clay p-6">
          <p>Delete removes {profile.displayName} from the room. This is for the preview desk and cannot be undone here.</p>
          <div className="mt-4 flex gap-2">
            <Button
              type="button"
              className={pill}
              onClick={() =>
                updateDesk((admin) =>
                  note(
                    { ...admin, removedMemberIds: [...new Set([...admin.removedMemberIds, profile.id])] },
                    `Removed ${profile.displayName}.`,
                  ),
                )
              }
            >
              Delete
            </Button>
            <Button type="button" variant="outline" className={`${pill} bg-transparent`} onClick={() => setConfirming(false)}>
              Keep them
            </Button>
          </div>
        </div>
      ) : null}

      {editing ? (
        <form
          className="mt-8 max-w-xl space-y-4"
          onSubmit={(event) => {
            event.preventDefault();
            save();
          }}
        >
          <label className="block space-y-2">
            <span className="text-sm">Display name</span>
            <Input className={fieldClass} value={draft.displayName} onChange={(event) => setDraft({ ...draft, displayName: event.target.value })} />
          </label>
          <label className="block space-y-2">
            <span className="text-sm">Bio</span>
            <Textarea className={areaClass} value={draft.bio} onChange={(event) => setDraft({ ...draft, bio: event.target.value })} />
          </label>
          <label className="block space-y-2">
            <span className="text-sm">Location</span>
            <Input className={fieldClass} value={draft.location} onChange={(event) => setDraft({ ...draft, location: event.target.value })} />
          </label>
          <fieldset>
            <legend className="text-sm">Why they joined</legend>
            <div className="mt-2 flex flex-wrap gap-2">
              {INTENTIONS.map((item) => {
                const on = draft.intentions.includes(item.id);
                return (
                  <button
                    key={item.id}
                    type="button"
                    aria-pressed={on}
                    className={`rounded-full px-4 py-2 text-sm ${on ? "bg-ink text-background" : "bg-white text-ink"}`}
                    onClick={() =>
                      setDraft({
                        ...draft,
                        intentions: on ? draft.intentions.filter((id) => id !== item.id) : [...draft.intentions, item.id],
                      })
                    }
                  >
                    {item.label}
                  </button>
                );
              })}
            </div>
          </fieldset>
          <Button type="submit" className={pill}>
            Save
          </Button>
        </form>
      ) : (
        <div className="mt-10 space-y-10">
          <section>
            <h2 className="font-serif text-3xl">Profile</h2>
            <p className="mt-3 text-ink-soft">{memberEmail(profile)}</p>
            <p className="mt-3 max-w-2xl text-lg leading-relaxed">{profile.bio || "No bio yet."}</p>
            {profile.location ? <p className="mt-2 text-ink-soft">{profile.location}</p> : null}
            <p className="mt-3 text-sm text-ink-soft">{profile.interests.map((item) => interestLabel(item)).filter(Boolean).join(" · ")}</p>
          </section>
          <section>
            <h2 className="font-serif text-3xl">Why they joined</h2>
            <p className="mt-3 text-lg">
              {profile.intentions.length
                ? profile.intentions
                    .map((item) => INTENTIONS.find((intention) => intention.id === item)?.label ?? item)
                    .join(" · ")
                : "They have not said yet."}
            </p>
          </section>
          <section>
            <h2 className="font-serif text-3xl">Waypoints</h2>
            <ul className="mt-3 space-y-2">
              {dossier.waypoints.length === 0 ? <li className="text-ink-soft">None yet.</li> : null}
              {dossier.waypoints.map((waypoint) => (
                <li key={waypoint.id}>{waypoint.name}</li>
              ))}
            </ul>
          </section>
          <section>
            <h2 className="font-serif text-3xl">Current seeds</h2>
            <ul className="mt-3 space-y-2">
              {dossier.seeds.filter((item) => item.status === "active").length === 0 ? <li className="text-ink-soft">Nothing active.</li> : null}
              {dossier.seeds
                .filter((item) => item.status === "active")
                .map((item) => (
                  <li key={item.id}>{item.seed?.title ?? "A seed"}</li>
                ))}
            </ul>
          </section>
          <section>
            <h2 className="font-serif text-3xl">Out There history</h2>
            <ul className="mt-3 space-y-2">
              {dossier.stories.length === 0 ? <li className="text-ink-soft">No stories yet.</li> : null}
              {dossier.stories.map((story) => (
                <li key={story.id}>
                  <Link href={`/out-there/${story.id}`} className="underline decoration-ink/15 underline-offset-4">
                    {story.title}
                  </Link>
                </li>
              ))}
            </ul>
          </section>
          <section>
            <h2 className="font-serif text-3xl">Campfire contributions</h2>
            <ul className="mt-3 space-y-2">
              {dossier.campfire.length === 0 ? <li className="text-ink-soft">Nothing said yet.</li> : null}
              {dossier.campfire.map((post) => (
                <li key={post.id}>{post.title}</li>
              ))}
            </ul>
          </section>
          <section>
            <h2 className="font-serif text-3xl">Skill swaps</h2>
            <ul className="mt-3 space-y-2">
              {dossier.swaps.length === 0 ? <li className="text-ink-soft">No exchanges yet.</li> : null}
              {dossier.swaps.map((swap) => (
                <li key={swap.id}>
                  With {swap.withName}. {swap.note}
                </li>
              ))}
            </ul>
          </section>
          <section>
            <h2 className="font-serif text-3xl">SAME partnerships</h2>
            <ul className="mt-3 space-y-2">
              {dossier.partnerships.length === 0 ? <li className="text-ink-soft">None yet.</li> : null}
              {dossier.partnerships.map((item) => (
                <li key={item.id}>
                  {item.seedTitle} · {item.status} · {item.withName}
                </li>
              ))}
            </ul>
          </section>
          {warnings.length > 0 ? (
            <section>
              <h2 className="font-serif text-3xl">Notes from the desk</h2>
              <ul className="mt-3 space-y-2">
                {warnings.map((item) => (
                  <li key={item.id}>{item.note}</li>
                ))}
              </ul>
            </section>
          ) : null}
          <Button
            type="button"
            variant="outline"
            className={`${pill} bg-transparent`}
            onClick={() => {
              const message = desk.settings.warnTemplate;
              updateDesk((admin) =>
                note(
                  {
                    ...admin,
                    warnings: [
                      { id: crypto.randomUUID(), userId: profile.id, note: message, createdAt: new Date().toISOString() },
                      ...admin.warnings,
                    ],
                  },
                  `Warned ${profile.displayName}.`,
                ),
              );
              notifyMember(profile.id, "A note from the steward", message);
            }}
          >
            Warn
          </Button>
        </div>
      )}
    </>
  );
}
