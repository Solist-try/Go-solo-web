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
import { formatRelative, kindLabel, sectionLabel } from "@/lib/format";
import { useGoSolo } from "@/lib/gosolo";
import { CAMPFIRE_KINDS, CAMPFIRE_SECTIONS, type CampfireKind, type CampfireSection } from "@/lib/types";

export function CampfireIndex() {
  const { world, user } = useGoSolo();
  const [section, setSection] = useState<CampfireSection | "all">("all");
  const posts = world.campfire.filter((post) => section === "all" || post.section === section);
  const names = new Map(world.profiles.map((profile) => [profile.id, profile]));

  return (
    <Frame>
      <PageIntro eyebrow="A shared campfire" title="Pull up a chair.">
        What&apos;s on your mind? Questions, thoughts, reflections, daily life, stories, celebrations,
        and challenges. A chair to return to after you have been out there.
      </PageIntro>
      <div className="mt-8">
        {user ? (
          <PrimaryLink href="/campfire/new">Say something</PrimaryLink>
        ) : (
          <PrimaryLink href="/register">Join to pull up a chair</PrimaryLink>
        )}
      </div>
      <div className="mt-8 flex flex-wrap gap-2" role="group" aria-label="Campfire sections">
        <SectionChip current={section === "all"} onClick={() => setSection("all")}>
          All
        </SectionChip>
        {CAMPFIRE_SECTIONS.map((item) => (
          <SectionChip key={item.id} current={section === item.id} onClick={() => setSection(item.id)}>
            {item.label}
          </SectionChip>
        ))}
      </div>
      {posts.length === 0 ? (
        <Panel className="mt-8">
          <p className="font-serif text-3xl">The chairs are here.</p>
          <p className="mt-3 text-lg text-ink-soft">The first thing said can be ordinary.</p>
        </Panel>
      ) : (
        <ul className="mt-8 space-y-4">
          {posts.map((post) => {
            const reply = [...world.comments]
              .filter((comment) => comment.targetId === post.id)
              .sort((a, b) => (a.createdAt < b.createdAt ? 1 : -1))[0];
            const replyAuthor = reply ? names.get(reply.authorId) : undefined;
            return (
              <li key={post.id}>
                <article className="rounded-[28px] bg-white/80 p-6 shadow-soft sm:p-8">
                  <p className="text-sm text-ink-soft">
                    {sectionLabel(post.section)} · {kindLabel(post.kind)}
                  </p>
                  <h2 className="mt-3 font-serif text-4xl leading-tight tracking-tight">
                    <Link href={`/campfire/${post.id}`}>{post.title}</Link>
                  </h2>
                  <p className="mt-4 text-lg leading-relaxed">{post.body}</p>
                  <div className="mt-5">
                    <AuthorLine
                      profile={names.get(post.authorId)}
                      meta={formatRelative(post.createdAt)}
                      href={`/profile/${post.authorId}`}
                    />
                  </div>
                  {reply ? (
                    <p className="mt-5 text-ink-soft">
                      {replyAuthor?.displayName}: {reply.body}
                    </p>
                  ) : null}
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

function SectionChip({
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
      className={`rounded-full px-4 py-2 text-sm ${current ? "bg-ink text-background" : "bg-white/80"}`}
    >
      {children}
    </button>
  );
}

export function CampfireDetail({ id }: { id: string }) {
  const { world, user, comment, desk } = useGoSolo();
  const post = world.campfire.find((item) => item.id === id);
  const locked = Boolean(desk.campfireModeration[id]?.locked);
  const [body, setBody] = useState("");
  if (!post) {
    return (
      <Frame>
        <PageIntro title="This conversation has quieted." />
      </Frame>
    );
  }
  const names = new Map(world.profiles.map((profile) => [profile.id, profile]));
  const replies = world.comments
    .filter((item) => item.targetType === "campfire" && item.targetId === post.id)
    .sort((a, b) => (a.createdAt > b.createdAt ? 1 : -1));

  return (
    <Frame>
      <p className="text-sm text-ink-soft">
        {sectionLabel(post.section)} · {kindLabel(post.kind)}
      </p>
      <h1 className="mt-3 max-w-3xl font-serif text-5xl leading-tight tracking-tight sm:text-6xl">{post.title}</h1>
      <p className="mt-6 max-w-3xl text-xl leading-relaxed">{post.body}</p>
      <div className="mt-6">
        <AuthorLine
          profile={names.get(post.authorId)}
          meta={formatRelative(post.createdAt)}
          href={`/profile/${post.authorId}`}
        />
      </div>
      <div className="mt-12 space-y-6">
        {replies.map((reply) => (
          <article key={reply.id} className="rounded-[28px] bg-white/70 p-6">
            <AuthorLine
              profile={names.get(reply.authorId)}
              meta={formatRelative(reply.createdAt)}
              href={`/profile/${reply.authorId}`}
            />
            <p className="mt-4 text-lg leading-relaxed">{reply.body}</p>
          </article>
        ))}
      </div>
      {locked ? (
        <p className="mt-8 text-lg text-ink-soft">This conversation is resting. The replies above can stay.</p>
      ) : user ? (
        <form
          className="mt-8 space-y-4"
          onSubmit={(event) => {
            event.preventDefault();
            void comment("campfire", post.id, body);
            setBody("");
          }}
        >
          <label className="block space-y-2">
            <span className="text-sm">Sit with this</span>
            <Textarea className={areaClass} value={body} maxLength={2000} onChange={(event) => setBody(event.target.value)} />
          </label>
          <Button type="submit" className={pill} disabled={!body.trim()}>
            Leave a reply
          </Button>
        </form>
      ) : (
        <p className="mt-8 text-lg text-ink-soft">
          <Link href="/register" className="underline decoration-ink/20 underline-offset-4">
            Join
          </Link>{" "}
          if you want to sit with this. The conversation stays readable either way.
        </p>
      )}
      <NextStep />
    </Frame>
  );
}

export function CampfireForm() {
  const params = useSearchParams();
  const router = useRouter();
  const { createCampfire } = useGoSolo();
  const [section, setSection] = useState<CampfireSection>(
    (params.get("section") as CampfireSection) || "general",
  );
  const [kind, setKind] = useState<CampfireKind>("thought");
  const [title, setTitle] = useState("");
  const [body, setBody] = useState("");
  const [error, setError] = useState("");
  const [pending, setPending] = useState(false);
  const waypoint = params.get("waypoint") || undefined;
  const seed = params.get("seed") || undefined;
  const outThere = params.get("outThere") || undefined;

  async function onSubmit(event: FormEvent) {
    event.preventDefault();
    if (!title.trim() || !body.trim()) {
      setError("A title and a few true sentences are enough.");
      return;
    }
    setPending(true);
    const id = await createCampfire({
      section,
      kind,
      title,
      body,
      waypointId: waypoint,
      seedId: seed,
      outThereId: outThere,
    });
    router.push(id ? `/campfire/${id}` : "/campfire");
  }

  return (
    <Frame>
      <PageIntro title="What's on your mind?">
        Ordinary is welcome. You do not need a conclusion.
      </PageIntro>
      <form onSubmit={onSubmit} className="mt-10 space-y-6">
        <fieldset>
          <legend className="text-sm">Where should this sit?</legend>
          <div className="mt-3 flex flex-wrap gap-2">
            {CAMPFIRE_SECTIONS.map((item) => (
              <button
                key={item.id}
                type="button"
                aria-pressed={section === item.id}
                onClick={() => setSection(item.id)}
                className={`rounded-full px-4 py-3 ${section === item.id ? "bg-ink text-background" : "bg-white"}`}
              >
                {item.label}
              </button>
            ))}
          </div>
        </fieldset>
        <fieldset>
          <legend className="text-sm">What kind of thing is it?</legend>
          <div className="mt-3 flex flex-wrap gap-2">
            {CAMPFIRE_KINDS.map((item) => (
              <button
                key={item.id}
                type="button"
                aria-pressed={kind === item.id}
                onClick={() => setKind(item.id)}
                className={`rounded-full px-4 py-3 ${kind === item.id ? "bg-ink text-background" : "bg-white"}`}
              >
                {item.label}
              </button>
            ))}
          </div>
        </fieldset>
        <label className="block space-y-2">
          <span className="text-sm">Title</span>
          <Input className={fieldClass} value={title} maxLength={120} onChange={(event) => setTitle(event.target.value)} />
        </label>
        <label className="block space-y-2">
          <span className="text-sm">The thing itself</span>
          <Textarea className={areaClass} value={body} maxLength={2000} onChange={(event) => setBody(event.target.value)} />
        </label>
        {error ? (
          <p role="alert" className="text-sm text-destructive">
            {error}
          </p>
        ) : null}
        <Button type="submit" className={pill} disabled={pending}>
          {pending ? "Setting a chair…" : "Share at the campfire"}
        </Button>
      </form>
    </Frame>
  );
}
