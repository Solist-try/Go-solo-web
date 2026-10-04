"use client";

import Link from "next/link";
import { createContext, useContext, useMemo, useState } from "react";
import { NextStep } from "@/components/gosolo/next-step";
import {
  AuthorLine,
  Frame,
  PageIntro,
  Panel,
  PrimaryLink,
  SecondaryLink,
  areaClass,
  fieldClass,
  pill,
} from "@/components/gosolo/pieces";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { useCatalog, useGoSolo } from "@/lib/gosolo";
import { categoryLabel, formatRelative } from "@/lib/format";
import { SEED_CATEGORIES, type SeedCategory } from "@/lib/types";
import { visibleSeeking } from "@/lib/view";

const NoteContext = createContext("");

export function SeedIndex() {
  const { seeds, user } = useGoSolo();
  const [category, setCategory] = useState<SeedCategory | "all">("all");
  const visible = seeds.filter((seed) => category === "all" || seed.category === category);

  return (
    <Frame>
      <PageIntro eyebrow="Possibility" title="Seeds">
        Small actions for a life of your own. A trip, a meal plan, a repair, a friend. Possibilities, at the size of a week.
      </PageIntro>
      <div className="mt-8 flex flex-wrap gap-2" role="group" aria-label="Filter seeds">
        <FilterChip current={category === "all"} onClick={() => setCategory("all")}>
          All
        </FilterChip>
        {SEED_CATEGORIES.map((item) => (
          <FilterChip key={item.id} current={category === item.id} onClick={() => setCategory(item.id)}>
            {item.label}
          </FilterChip>
        ))}
      </div>
      <ul className="mt-8 grid gap-4 md:grid-cols-2">
        {visible.map((seed) => (
          <li key={seed.id}>
            <Link href={`/seeds/${seed.id}`} className="block h-full rounded-[28px] bg-white/80 p-6 shadow-soft sm:p-8">
              <p className="text-sm text-ink-soft">
                {categoryLabel(seed.category)} · {seed.timeframe}
              </p>
              <h2 className="mt-3 font-serif text-3xl leading-tight tracking-tight text-ink">{seed.title}</h2>
              <p className="mt-4 leading-relaxed text-ink-soft">{seed.description}</p>
            </Link>
          </li>
        ))}
      </ul>
      {user ? <NextStep /> : (
        <Panel tone="gold" className="mt-12">
          <h2 className="font-serif text-3xl">Begin when you are ready.</h2>
          <p className="mt-3 text-lg text-ink-soft">Joining lets you keep a seed, then go out and bring the story back.</p>
          <div className="mt-6">
            <PrimaryLink href="/register">Join Go Solo</PrimaryLink>
          </div>
        </Panel>
      )}
    </Frame>
  );
}

function FilterChip({
  current,
  onClick,
  children,
}: {
  current: boolean;
  onClick: () => void;
  children: string;
}) {
  return (
    <button
      type="button"
      aria-pressed={current}
      onClick={onClick}
      className={`rounded-full px-4 py-2 text-sm ${current ? "bg-ink text-background" : "bg-white/80 text-ink"}`}
    >
      {children}
    </button>
  );
}

export function SeedDetail({ id }: { id: string }) {
  const { getSeed, getWaypoint } = useCatalog();
  const seed = getSeed(id);
  const {
    user,
    world,
    beginSeed,
    setSeedStatus,
    checkInSeed,
    matchWith,
    partnershipCheckIn,
    offerSkill,
    requestSkill,
  } = useGoSolo();
  const [goal, setGoal] = useState("");
  const [note, setNote] = useState("");
  const [checkIn, setCheckIn] = useState("");
  const [skill, setSkill] = useState("");
  const [detail, setDetail] = useState("");
  const [skillError, setSkillError] = useState("");
  const [hello, setHello] = useState("");

  const mine = world.userSeeds.find(
    (item) => item.userId === user?.id && item.seedId === id && item.status !== "completed",
  );
  const names = useMemo(
    () => new Map(world.profiles.map((profile) => [profile.id, profile])),
    [world.profiles],
  );
  const seeking = visibleSeeking(world.partnerships).filter(
    (item) => item.seedId === id && item.seekerId !== user?.id,
  );
  const partnership = world.partnerships.find(
    (item) =>
      item.seedId === id &&
      item.status === "matched" &&
      (item.seekerId === user?.id || item.partnerId === user?.id),
  );

  if (!seed) {
    return (
      <Frame>
        <PageIntro title="This seed is not here.">
          It may have been a path from another season. The rest of the garden is still open.
        </PageIntro>
        <div className="mt-8">
          <PrimaryLink href="/seeds">Back to seeds</PrimaryLink>
        </div>
      </Frame>
    );
  }

  const loginHref = `/register?next=${encodeURIComponent(`/seeds/${seed.id}`)}`;

  return (
    <Frame>
      <PageIntro eyebrow={`${categoryLabel(seed.category)} · ${seed.timeframe}`} title={seed.title}>
        {seed.description}
      </PageIntro>
      <Panel tone="sage" className="mt-10">
        <p className="text-sm text-ink-soft">The action</p>
        <p className="mt-3 font-serif text-3xl leading-snug tracking-tight text-ink">{seed.prompt}</p>
        {seed.kind === "same" ? (
          <p className="mt-6 text-ink-soft">
            SAME means Support, Accountability, Mutual, Empowerment. A partner for the stretch, not an audience.
          </p>
        ) : null}
      </Panel>

      {!user ? (
        <div className="mt-8">
          <PrimaryLink href={loginHref}>Join to begin this seed</PrimaryLink>
        </div>
      ) : null}

      {user && seed.kind === "practice" ? (
        <Panel className="mt-4">
          {mine ? (
            <div className="space-y-5">
              <p className="text-lg">You are with this seed. Missing a day is allowed.</p>
              <label className="block space-y-2">
                <span className="text-sm">How did it go?</span>
                <Textarea className={areaClass} value={note} onChange={(event) => setNote(event.target.value)} />
              </label>
              <div className="flex flex-wrap gap-3">
                <Button className={pill} onClick={() => { void checkInSeed(mine.id, note); setNote(""); }}>
                  Leave a note
                </Button>
                <SecondaryLink href={`/out-there/new?seed=${seed.id}`}>This wants to be a story</SecondaryLink>
                <Button variant="outline" className={`${pill} bg-transparent`} onClick={() => void setSeedStatus(mine.id, "resting")}>
                  Let it rest
                </Button>
              </div>
              {mine.checkIns.length > 0 ? (
                <ul className="space-y-3">
                  {mine.checkIns.map((item) => (
                    <li key={item.id} className="text-ink-soft">
                      <span className="text-ink">{item.note}</span>
                      <span className="mt-1 block text-sm">{formatRelative(item.at)}</span>
                    </li>
                  ))}
                </ul>
              ) : null}
            </div>
          ) : (
            <Button className={pill} onClick={() => void beginSeed(seed.id)}>
              Begin this seed
            </Button>
          )}
        </Panel>
      ) : null}

      {user && seed.kind === "same" ? (
        <Panel className="mt-4">
          <h2 className="font-serif text-3xl">A partner for this</h2>
          <p className="mt-3 text-ink-soft">Weekly, briefly, without advice unless you ask.</p>
          {partnership ? (
            <div className="mt-6 space-y-4">
              <p className="text-lg">
                You and{" "}
                {
                  names.get(
                    partnership.seekerId === user.id ? partnership.partnerId || "" : partnership.seekerId,
                  )?.displayName
                }{" "}
                are walking this together.
              </p>
              <p className="text-ink">{partnership.goal}</p>
              <label className="block space-y-2">
                <span className="text-sm">This week&apos;s note</span>
                <Textarea className={areaClass} value={checkIn} onChange={(event) => setCheckIn(event.target.value)} />
              </label>
              <Button
                className={pill}
                onClick={() => {
                  void partnershipCheckIn(partnership.id, checkIn);
                  setCheckIn("");
                }}
              >
                Leave this week&apos;s note
              </Button>
              <ul className="space-y-3">
                {partnership.checkIns.map((item) => (
                  <li key={item.id}>
                    <p>{item.note}</p>
                    <p className="text-sm text-ink-soft">
                      {names.get(item.userId || "")?.displayName} · {formatRelative(item.at)}
                    </p>
                  </li>
                ))}
              </ul>
            </div>
          ) : (
            <div className="mt-6 space-y-6">
              <label className="block space-y-2">
                <span className="text-sm">What are you hoping to practice?</span>
                <Textarea className={areaClass} value={goal} onChange={(event) => setGoal(event.target.value)} />
              </label>
              {seeking.length > 0 ? (
                <ul className="space-y-4">
                  {seeking.map((item) => {
                    const person = names.get(item.seekerId);
                    return (
                      <li key={item.id} className="rounded-[24px] bg-gold/70 p-5">
                        <AuthorLine profile={person} href={person ? `/profile/${person.id}` : undefined} />
                        <p className="mt-3">{item.goal}</p>
                        <Button
                          className={`${pill} mt-4`}
                          onClick={() => void matchWith(seed.id, item.seekerId, goal || item.goal)}
                        >
                          Walk with {person?.displayName?.split(" ")[0]}
                        </Button>
                      </li>
                    );
                  })}
                </ul>
              ) : (
                <p className="text-ink-soft">No one is waiting on this seed yet. You can be the open chair.</p>
              )}
              {!mine ? (
                <Button className={pill} onClick={() => void beginSeed(seed.id, goal)}>
                  I am open to a partner
                </Button>
              ) : (
                <p className="text-sm text-ink-soft">You are open. Someone can find you here.</p>
              )}
            </div>
          )}
        </Panel>
      ) : null}

      {seed.kind === "skill-swap" ? (
        <NoteContext.Provider value={hello}>
          <SkillBoard />
        </NoteContext.Provider>
      ) : null}

      {user && seed.kind === "skill-swap" ? (
        <Panel className="mt-4">
          <h2 className="font-serif text-3xl">Offer a skill. Learn a skill.</h2>
          <div className="mt-5 grid gap-4 sm:grid-cols-2">
            <label className="block space-y-2">
              <span className="text-sm">Skill</span>
              <Input className={fieldClass} value={skill} onChange={(event) => setSkill(event.target.value)} placeholder="Photography" />
            </label>
            <label className="block space-y-2 sm:col-span-2">
              <span className="text-sm">A little about it</span>
              <Textarea className={areaClass} value={detail} onChange={(event) => setDetail(event.target.value)} />
            </label>
          </div>
          {skillError ? <p className="mt-3 text-sm text-destructive">{skillError}</p> : null}
          <div className="mt-5 flex flex-wrap gap-3">
            <Button
              className={pill}
              onClick={async () => {
                const result = await offerSkill(skill, detail);
                setSkillError(result.error ?? "");
                if (!result.error) {
                  setSkill("");
                  setDetail("");
                }
              }}
            >
              Offer this
            </Button>
            <Button
              variant="outline"
              className={`${pill} bg-transparent`}
              onClick={async () => {
                const result = await requestSkill(skill, detail);
                setSkillError(result.error ?? "");
                if (!result.error) {
                  setSkill("");
                  setDetail("");
                }
              }}
            >
              Learn this
            </Button>
          </div>
          <p className="mt-6 text-sm leading-relaxed text-ink-soft">
            When you connect, a short hello goes with you. After the exchange, it can become an Out There story.
          </p>
          <label className="mt-4 block space-y-2">
            <span className="text-sm">A note, if you reach out</span>
            <Input className={fieldClass} value={hello} onChange={(event) => setHello(event.target.value)} />
          </label>
          <div className="mt-6">
            <SecondaryLink href="/out-there/new?seed=skill-swap">When it becomes a story</SecondaryLink>
          </div>
        </Panel>
      ) : null}

      <div className="mt-8 flex flex-wrap gap-4 text-sm">
        {seed.waypoints.map((waypointId) => {
          const waypoint = getWaypoint(waypointId);
          return waypoint ? (
            <Link key={waypoint.id} href={`/waypoints/${waypoint.slug}`} className="underline decoration-ink/20 underline-offset-4">
              {waypoint.name}
            </Link>
          ) : null;
        })}
      </div>
      {user ? <NextStep /> : null}
    </Frame>
  );
}

function SkillBoard() {
  const { world, user } = useGoSolo();
  const note = useNote();
  const names = new Map(world.profiles.map((profile) => [profile.id, profile]));
  return (
    <div className="mt-4 grid gap-4 lg:grid-cols-2">
      <Panel tone="gold">
        <h2 className="font-serif text-3xl">Offer a skill</h2>
        <ul className="mt-5 space-y-5">
          {world.offers.map((offer) => (
            <li key={offer.id}>
              <AuthorLine profile={names.get(offer.userId)} href={user ? `/profile/${offer.userId}` : undefined} />
              <h3 className="mt-3 text-lg">{offer.skill}</h3>
              <p className="text-ink-soft">{offer.description}</p>
              {user && offer.userId !== user.id ? (
                <ConnectButton toUserId={offer.userId} offerId={offer.id} note={note} />
              ) : null}
            </li>
          ))}
        </ul>
      </Panel>
      <Panel tone="mist">
        <h2 className="font-serif text-3xl">Learn a skill</h2>
        <ul className="mt-5 space-y-5">
          {world.requests.map((request) => (
            <li key={request.id}>
              <AuthorLine profile={names.get(request.userId)} href={user ? `/profile/${request.userId}` : undefined} />
              <h3 className="mt-3 text-lg">{request.skill}</h3>
              <p className="text-ink-soft">{request.description}</p>
              {user && request.userId !== user.id ? (
                <ConnectButton toUserId={request.userId} requestId={request.id} note={note} />
              ) : null}
            </li>
          ))}
        </ul>
      </Panel>
    </div>
  );
}

function useNote() {
  return useContext(NoteContext);
}

function ConnectButton({
  toUserId,
  offerId,
  requestId,
  note,
}: {
  toUserId: string;
  offerId?: string;
  requestId?: string;
  note: string;
}) {
  const { connectSkill, world, user } = useGoSolo();
  const existing = world.connections.some(
    (item) =>
      item.fromUserId === user?.id &&
      item.toUserId === toUserId &&
      (item.offerId === offerId || item.requestId === requestId),
  );
  if (existing) return <p className="mt-3 text-sm text-ink-soft">You already reached out.</p>;
  return (
    <Button
      className={`${pill} mt-4`}
      onClick={() =>
        void connectSkill({
          toUserId,
          offerId,
          requestId,
          note: note.trim() || "I would like to trade an hour.",
        })
      }
    >
      Connect
    </Button>
  );
}
