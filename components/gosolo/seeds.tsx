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
import { SEEDS } from "@/lib/catalog";
import { useCatalog, useGoSolo } from "@/lib/gosolo";
import { categoryLabel, formatRelative } from "@/lib/format";
import { visibleSeeking } from "@/lib/view";

const NoteContext = createContext("");

const SAME_EXAMPLES = [
  "Build confidence",
  "Create routines",
  "Improve wellbeing",
  "Travel independently",
  "Make new friends",
  "Navigate a life transition",
];

const SWAP_EXAMPLES = [
  "Teach Crochet",
  "Learn Spanish",
  "Teach Gardening",
  "Learn Budgeting",
  "Teach Writing",
  "Learn DIY",
];

const STARTER_IDS = ["learning-spanish", "building-confidence", "making-local-friends"];

const GROWING_SOON = [
  "Accountability Circles",
  "Starting Over Buddy",
  "Medical Buddy",
  "Project Partners",
  "Learning Pods",
];

const JOURNEY = [
  { label: "Plant Seed", href: "#plant" },
  { label: "Find Support", href: "/seeds/weekly-hello" },
  { label: "Take Action" },
  { label: "Share Experience In Out There", href: "/out-there" },
  { label: "Reflect At Campfire", href: "/campfire" },
  { label: "Plant Another Seed", href: "#plant" },
];

export function SeedIndex() {
  return (
    <Frame>
      <header className="max-w-3xl">
        <p className="text-sm text-ink-soft">Growth, connection and possibility</p>
        <h1 className="mt-4 font-serif text-5xl leading-[1.05] tracking-tight text-ink sm:text-7xl">Seeds</h1>
        <p className="mt-6 font-serif text-3xl leading-snug tracking-tight text-ink sm:text-4xl">
          Tiny futures you can plant now.
        </p>
        <div className="mt-8 max-w-2xl space-y-4 text-lg leading-relaxed text-ink sm:text-xl">
          <p>Some seeds grow through accountability.</p>
          <p>Some through learning.</p>
          <p>Some through encouragement.</p>
          <p>Some through practice.</p>
          <p>All begin with something small.</p>
        </div>
        <p className="mt-10 max-w-2xl font-serif text-3xl leading-snug tracking-tight text-ink sm:text-4xl">
          What would I like to grow?
        </p>
        <p className="mt-4 max-w-2xl text-lg leading-relaxed text-ink">
          A seed grows through attention, practice, support, accountability, encouragement, and connection.
        </p>
        <ul className="mt-8 max-w-xl space-y-3">
          {STARTER_IDS.map((id) => {
            const seed = SEEDS.find((item) => item.id === id);
            if (!seed) return null;
            return (
              <li key={seed.id}>
                <Link
                  href={`/seeds/${seed.id}`}
                  className="font-serif text-3xl tracking-tight text-ink underline decoration-ink/20 underline-offset-4"
                >
                  {seed.title}
                </Link>
              </li>
            );
          })}
        </ul>
      </header>

      <div id="plant" className="mt-16 grid scroll-mt-24 gap-6 lg:grid-cols-2">
        <article className="flex h-full flex-col rounded-[28px] bg-sage p-7 shadow-soft sm:p-10">
          <h2 className="font-serif text-5xl tracking-tight text-ink sm:text-6xl">SAME</h2>
          <p className="mt-4 text-sm leading-relaxed text-ink">
            Support · Accountability · Mutual · Empowerment
          </p>
          <div className="mt-6 space-y-4 text-lg leading-relaxed text-ink">
            <p>A seed grows faster when someone else helps tend it.</p>
            <p>Choose something you&apos;d like to move towards and find someone to grow alongside.</p>
          </div>
          <p className="mt-8 text-sm text-ink-soft">Examples</p>
          <ul className="mt-3 space-y-1 text-lg text-ink">
            {SAME_EXAMPLES.map((item) => (
              <li key={item}>{item}</li>
            ))}
          </ul>
          <div className="mt-8">
            <PrimaryLink href="/seeds/weekly-hello">Find a SAME Partner</PrimaryLink>
          </div>
        </article>

        <article className="flex h-full flex-col rounded-[28px] bg-clay p-7 shadow-soft sm:p-10">
          <h2 className="font-serif text-5xl tracking-tight text-ink sm:text-6xl">Skill Swap</h2>
          <p className="mt-4 font-serif text-2xl leading-snug tracking-tight text-ink">
            Learn something. Teach something.
          </p>
          <div className="mt-6 space-y-4 text-lg leading-relaxed text-ink">
            <p>Offer what you know.</p>
            <p>Ask for what you&apos;d like to learn.</p>
            <p>Help somebody else&apos;s seed grow while growing your own.</p>
          </div>
          <p className="mt-8 text-sm text-ink-soft">Examples</p>
          <ul className="mt-3 space-y-1 text-lg text-ink">
            {SWAP_EXAMPLES.map((item) => (
              <li key={item}>{item}</li>
            ))}
          </ul>
          <div className="mt-8">
            <PrimaryLink href="/seeds/skill-swap">Explore Skill Swaps</PrimaryLink>
          </div>
        </article>
      </div>

      <section className="mt-16" aria-labelledby="growing-soon-title">
        <h2 id="growing-soon-title" className="font-serif text-4xl tracking-tight text-ink sm:text-5xl">
          Growing Soon
        </h2>
        <p className="mt-4 max-w-2xl text-lg leading-relaxed text-ink">These are not open yet.</p>
        <ul className="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
          {GROWING_SOON.map((item) => (
            <li key={item} className="rounded-[28px] bg-mist px-6 py-5 font-serif text-2xl tracking-tight text-ink">
              {item}
            </li>
          ))}
        </ul>
      </section>

      <section className="mt-16 max-w-3xl" aria-labelledby="journey-title">
        <h2 id="journey-title" className="font-serif text-4xl tracking-tight text-ink sm:text-5xl">
          How a seed grows
        </h2>
        <ol className="mt-8">
          {JOURNEY.map((step, index) => (
            <li key={step.label}>
              {step.href ? (
                <Link
                  href={step.href}
                  className="font-serif text-2xl tracking-tight text-ink underline decoration-ink/20 underline-offset-4 sm:text-3xl"
                >
                  {step.label}
                </Link>
              ) : (
                <span className="font-serif text-2xl tracking-tight text-ink sm:text-3xl">{step.label}</span>
              )}
              {index < JOURNEY.length - 1 ? (
                <p className="py-2 text-ink-soft" aria-hidden="true">
                  ↓
                </p>
              ) : null}
            </li>
          ))}
        </ol>
      </section>
    </Frame>
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
        <p className="text-sm text-ink-soft">
          {seed.kind === "same" ? "What you are growing" : seed.kind === "skill-swap" ? "The exchange" : "The action"}
        </p>
        <p className="mt-3 font-serif text-3xl leading-snug tracking-tight text-ink">{seed.prompt}</p>
        {seed.kind === "same" ? (
          <p className="mt-6 text-ink-soft">
            SAME means Support, Accountability, Mutual, Empowerment. A partner for the stretch, not an audience.
          </p>
        ) : null}
      </Panel>

      {!user ? (
        <div className="mt-8">
          <p className="max-w-xl text-lg leading-relaxed text-ink">
            Take a look around.{" "}
            <Link href={loginHref} className="underline decoration-ink/20 underline-offset-4">
              Join Go Solo
            </Link>{" "}
            if you want to start with this seed.
          </p>
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
          <h2 className="font-serif text-3xl">Find a SAME Partner</h2>
          <p className="mt-3 text-ink-soft">Name what you would like to grow. A partner tends it with you, briefly, and without advice unless you ask.</p>
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
                <span className="text-sm">What would you like to grow?</span>
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
