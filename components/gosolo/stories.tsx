"use client";

import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";
import { useState, type FormEvent } from "react";
import { NextStep } from "@/components/gosolo/next-step";
import {
  AuthorLine,
  Frame,
  PageIntro,
  Panel,
  PrimaryLink,
  areaClass,
  fieldClass,
  pill,
} from "@/components/gosolo/pieces";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { againLabel, describeReactions, formatRelative } from "@/lib/format";
import { useCatalog, useGoSolo } from "@/lib/gosolo";
import { REACTIONS, WOULD_AGAIN, type WouldAgain } from "@/lib/types";

export function StoryIndex() {
  const { world } = useGoSolo();
  const names = new Map(world.profiles.map((profile) => [profile.id, profile]));

  return (
    <Frame>
      <PageIntro eyebrow="Experiences, not achievements" title="Out There">
        What did you do? What were you expecting? What actually happened? Would you do it again?
      </PageIntro>
      <div className="mt-8">
        <PrimaryLink href="/out-there/new">Bring a story back</PrimaryLink>
      </div>
      {world.stories.length === 0 ? (
        <Panel className="mt-10">
          <p className="font-serif text-3xl leading-snug">No one has brought a story back yet.</p>
          <p className="mt-4 text-lg text-ink-soft">You could be the first, or you can wait until you have one. Both are welcome.</p>
        </Panel>
      ) : (
        <ul className="mt-10 space-y-4">
          {world.stories.map((story) => {
            const reactions = world.reactions.filter((reaction) => reaction.targetId === story.id);
            const nameMap = new Map(world.profiles.map((profile) => [profile.id, profile.displayName]));
            return (
              <li key={story.id}>
                <article className="rounded-[28px] bg-white/80 p-6 shadow-soft sm:p-8">
                  <AuthorLine
                    profile={names.get(story.authorId)}
                    meta={formatRelative(story.createdAt)}
                    href={`/profile/${story.authorId}`}
                  />
                  <h2 className="mt-5 font-serif text-4xl leading-tight tracking-tight">
                    <Link href={`/out-there/${story.id}`}>{story.title}</Link>
                  </h2>
                  <p className="mt-4 text-lg leading-relaxed text-ink-soft">{story.actuallyHappened}</p>
                  <p className="mt-4 text-sm text-ink">Would you do it again? {againLabel(story.wouldDoAgain)}</p>
                  <ul className="mt-4 space-y-1 text-sm text-ink-soft">
                    {REACTIONS.map((reaction) => {
                      const line = describeReactions(reactions, nameMap, reaction.id);
                      return line ? <li key={reaction.id}>{line}</li> : null;
                    })}
                  </ul>
                </article>
              </li>
            );
          })}
        </ul>
      )}
      <NextStep />
    </Frame>
  );
}

export function StoryDetail({ id }: { id: string }) {
  const { world, user, reactToStory } = useGoSolo();
  const { getSeed, getWaypoint } = useCatalog();
  const story = world.stories.find((item) => item.id === id);
  if (!story) {
    return (
      <Frame>
        <PageIntro title="This story has moved on.">
          The rest of Out There is still here.
        </PageIntro>
      </Frame>
    );
  }
  const profile = world.profiles.find((item) => item.id === story.authorId);
  const seed = story.seedId ? getSeed(story.seedId) : undefined;
  const waypoint = story.waypointId ? getWaypoint(story.waypointId) : undefined;
  const discussion = world.campfire.find((post) => post.outThereId === story.id);
  const reactions = world.reactions.filter((reaction) => reaction.targetId === story.id);
  const nameMap = new Map(world.profiles.map((item) => [item.id, item.displayName]));
  const mine = reactions.find((reaction) => reaction.userId === user?.id);

  return (
    <Frame>
      <AuthorLine profile={profile} meta={formatRelative(story.createdAt)} href={`/profile/${story.authorId}`} />
      <h1 className="mt-6 max-w-3xl font-serif text-5xl leading-tight tracking-tight sm:text-6xl">{story.title}</h1>
      <div className="mt-10 space-y-8">
        <Prompt label="What did you do?" body={story.whatDidYouDo} />
        <Prompt label="What were you expecting?" body={story.expecting} />
        <Prompt label="What actually happened?" body={story.actuallyHappened} />
        <Prompt label="Would you do it again?" body={againLabel(story.wouldDoAgain)} />
      </div>
      <div className="mt-10 flex flex-wrap gap-3" role="group" aria-label="Responses">
        {REACTIONS.map((reaction) => (
          <Button
            key={reaction.id}
            type="button"
            variant="outline"
            aria-pressed={mine?.kind === reaction.id}
            className={`${pill} bg-transparent ${mine?.kind === reaction.id ? "bg-ink text-background hover:bg-ink" : ""}`}
            onClick={() => void reactToStory(story.id, reaction.id)}
          >
            {reaction.label}
          </Button>
        ))}
      </div>
      <ul className="mt-4 space-y-1 text-sm text-ink-soft">
        {REACTIONS.map((reaction) => {
          const line = describeReactions(reactions, nameMap, reaction.id);
          return line ? <li key={reaction.id}>{line}</li> : null;
        })}
      </ul>
      <Panel tone="mist" className="mt-10">
        <h2 className="font-serif text-3xl">Connected to</h2>
        <ul className="mt-4 space-y-2 text-lg">
          {seed ? (
            <li>
              <Link href={`/seeds/${seed.id}`} className="underline decoration-ink/20 underline-offset-4">
                Seed · {seed.title}
              </Link>
            </li>
          ) : null}
          {waypoint ? (
            <li>
              <Link href={`/waypoints/${waypoint.slug}`} className="underline decoration-ink/20 underline-offset-4">
                Waypoint · {waypoint.name}
              </Link>
            </li>
          ) : null}
          <li>
            {discussion ? (
              <Link href={`/campfire/${discussion.id}`} className="underline decoration-ink/20 underline-offset-4">
                At the campfire
              </Link>
            ) : (
              <Link
                href={`/campfire/new?outThere=${story.id}&seed=${story.seedId ?? ""}&waypoint=${story.waypointId ?? ""}`}
                className="underline decoration-ink/20 underline-offset-4"
              >
                Continue this at the campfire
              </Link>
            )}
          </li>
          <li>
            <Link href={`/profile/${story.authorId}`} className="underline decoration-ink/20 underline-offset-4">
              {profile?.displayName || "Author"}
            </Link>
          </li>
        </ul>
      </Panel>
      <NextStep />
    </Frame>
  );
}

function Prompt({ label, body }: { label: string; body: string }) {
  return (
    <div>
      <h2 className="text-sm text-ink-soft">{label}</h2>
      <p className="mt-2 max-w-3xl font-serif text-3xl leading-snug tracking-tight">{body}</p>
    </div>
  );
}

export function StoryForm() {
  const params = useSearchParams();
  const router = useRouter();
  const { createStory, user, world } = useGoSolo();
  const { getSeed, getWaypoint } = useCatalog();
  const [title, setTitle] = useState("");
  const [what, setWhat] = useState("");
  const [expecting, setExpecting] = useState("");
  const [happened, setHappened] = useState("");
  const [again, setAgain] = useState<WouldAgain>("yes");
  const [seedId, setSeedId] = useState(params.get("seed") ?? "");
  const [waypointId, setWaypointId] = useState(params.get("waypoint") ?? "");
  const [error, setError] = useState("");
  const [pending, setPending] = useState(false);
  const mySeeds = world.userSeeds.filter((item) => item.userId === user?.id);

  async function onSubmit(event: FormEvent) {
    event.preventDefault();
    if (!title.trim() || !what.trim() || !expecting.trim() || !happened.trim()) {
      setError("All four questions want an answer, even a short one.");
      return;
    }
    setPending(true);
    const id = await createStory({
      title,
      whatDidYouDo: what,
      expecting,
      actuallyHappened: happened,
      wouldDoAgain: again,
      seedId: seedId || undefined,
      waypointId: waypointId || undefined,
    });
    setPending(false);
    router.push(id ? `/out-there/${id}` : "/out-there");
  }

  return (
    <Frame>
      <PageIntro title="Bring a story back.">
        Not an achievement. An experience. Tell it the size it actually was.
      </PageIntro>
      <form onSubmit={onSubmit} className="mt-10 space-y-5">
        <label className="block space-y-2">
          <span className="text-sm">A title</span>
          <Input className={fieldClass} value={title} maxLength={120} onChange={(event) => setTitle(event.target.value)} />
        </label>
        <Field label="What did you do?" value={what} onChange={setWhat} />
        <Field label="What were you expecting?" value={expecting} onChange={setExpecting} />
        <Field label="What actually happened?" value={happened} onChange={setHappened} />
        <fieldset>
          <legend className="text-sm">Would you do it again?</legend>
          <div className="mt-3 flex flex-wrap gap-2">
            {WOULD_AGAIN.map((option) => (
              <button
                key={option.id}
                type="button"
                aria-pressed={again === option.id}
                onClick={() => setAgain(option.id)}
                className={`rounded-full px-4 py-3 ${again === option.id ? "bg-ink text-background" : "bg-white"}`}
              >
                {option.label}
              </button>
            ))}
          </div>
        </fieldset>
        <label className="block space-y-2">
          <span className="text-sm">Related seed, if there is one</span>
          <select className={`${fieldClass} w-full`} value={seedId} onChange={(event) => setSeedId(event.target.value)}>
            <option value="">None</option>
            {mySeeds.map((item) => {
              const seed = getSeed(item.seedId);
              return seed ? (
                <option key={item.id} value={seed.id}>
                  {seed.title}
                </option>
              ) : null;
            })}
          </select>
        </label>
        <label className="block space-y-2">
          <span className="text-sm">Related waypoint, if there is one</span>
          <select
            className={`${fieldClass} w-full`}
            value={waypointId}
            onChange={(event) => setWaypointId(event.target.value)}
          >
            <option value="">None</option>
            {world.memberships
              .filter((item) => item.userId === user?.id)
              .map((item) => {
                const waypoint = getWaypoint(item.waypointId);
                return waypoint ? (
                  <option key={waypoint.id} value={waypoint.id}>
                    {waypoint.name}
                  </option>
                ) : null;
              })}
          </select>
        </label>
        {error ? (
          <p role="alert" className="text-sm text-destructive">
            {error}
          </p>
        ) : null}
        <Button type="submit" className={pill} disabled={pending}>
          {pending ? "Setting it down…" : "Share the experience"}
        </Button>
      </form>
    </Frame>
  );
}

function Field({
  label,
  value,
  onChange,
}: {
  label: string;
  value: string;
  onChange: (value: string) => void;
}) {
  return (
    <label className="block space-y-2">
      <span className="text-sm">{label}</span>
      <Textarea className={areaClass} value={value} maxLength={2000} onChange={(event) => onChange(event.target.value)} />
    </label>
  );
}
