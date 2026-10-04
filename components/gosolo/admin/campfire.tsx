"use client";

import { DeskIntro } from "@/components/gosolo/admin/ui";
import { ModeratePost } from "@/components/gosolo/admin/moderation";
import { buildInsights } from "@/lib/admin/insights";
import { campfireFlags, type PostModeration } from "@/lib/admin/state";
import { formatDate, kindLabel } from "@/lib/format";
import { useGoSolo } from "@/lib/gosolo";
import type { CampfirePost } from "@/lib/types";

function ConversationBlock({
  title,
  items,
  desk,
}: {
  title: string;
  items: (CampfirePost & PostModeration & { replies?: number })[];
  desk: ReturnType<typeof useGoSolo>["desk"];
}) {
  return (
    <section className="mt-12">
      <h2 className="font-serif text-3xl text-ink">{title}</h2>
      {items.length === 0 ? <p className="mt-3 text-ink-soft">Nothing in this pile.</p> : null}
      <ul className="mt-4 space-y-6">
        {items.map((post) => {
          const flags = campfireFlags(desk, post);
          return (
            <li key={`${title}-${post.id}`} className="rounded-[28px] bg-white/70 p-6">
              <p className="text-sm text-ink-soft">
                {kindLabel(post.kind)} · {formatDate(post.createdAt)}
                {flags.hidden ? " · Hidden" : ""}
                {flags.locked ? " · Resting" : ""}
                {flags.pinned ? " · Pinned" : ""}
                {typeof post.replies === "number" ? ` · ${post.replies} replies` : ""}
              </p>
              <h3 className="mt-2 font-serif text-2xl text-ink">{post.title}</h3>
              <div className="mt-4">
                <ModeratePost kind="campfire" id={post.id} title={post.title} authorId={post.authorId} flags={flags} />
              </div>
            </li>
          );
        })}
      </ul>
    </section>
  );
}

export function CampfireAdmin() {
  const { library, desk, seeds, waypoints } = useGoSolo();
  const insight = buildInsights({ world: library, seeds, waypoints });
  const posts = library.campfire
    .map((post) => campfireFlags(desk, post))
    .filter((post) => !post.removed)
    .sort((a, b) => (a.createdAt < b.createdAt ? 1 : -1));

  return (
    <>
      <DeskIntro eyebrow="Campfire" title="Keep the conversation habitable.">
        Recent talk, the busiest threads, open questions, and posts still waiting for a reply.
      </DeskIntro>
      <ConversationBlock title="Recent conversations" items={posts.slice(0, 6)} desk={desk} />
      <ConversationBlock
        title="Most active conversations"
        desk={desk}
        items={insight.activeConversations.map((item) => ({ ...campfireFlags(desk, item.post), replies: item.replies }))}
      />
      <ConversationBlock title="Open questions" desk={desk} items={insight.openQuestions.map((post) => campfireFlags(desk, post))} />
      <ConversationBlock title="Unanswered posts" desk={desk} items={insight.unanswered.map((post) => campfireFlags(desk, post))} />
    </>
  );
}
