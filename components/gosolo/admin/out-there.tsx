"use client";

import { DeskIntro, QuietTable, ShareList } from "@/components/gosolo/admin/ui";
import { ModeratePost } from "@/components/gosolo/admin/moderation";
import { buildInsights } from "@/lib/admin/insights";
import { storyFlags } from "@/lib/admin/state";
import { categoryLabel, formatDate } from "@/lib/format";
import { useGoSolo } from "@/lib/gosolo";

export function OutThereAdmin() {
  const { library, desk, seeds, waypoints } = useGoSolo();
  const insight = buildInsights({ world: library, seeds, waypoints });
  const names = new Map(library.profiles.map((profile) => [profile.id, profile.displayName]));
  const stories = library.stories
    .map((story) => storyFlags(desk, story))
    .filter((story) => !story.removed)
    .sort((a, b) => (a.createdAt < b.createdAt ? 1 : -1));

  return (
    <>
      <DeskIntro eyebrow="Out There" title="Real-world action.">
        What people actually did. Feature, pin, hide, or delete when the room needs it.
      </DeskIntro>
      <QuietTable>
        <caption className="sr-only">Out There posts</caption>
        <thead className="text-sm text-ink-soft">
          <tr>
            {["Post", "Author", "Date", "Category", "Comments", "Reactions"].map((heading) => (
              <th key={heading} className="px-3 py-3 font-normal">
                {heading}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {stories.map((story) => {
            const category = seeds.find((seed) => seed.id === story.seedId)?.category;
            const comments = library.comments.filter((comment) => comment.targetType === "out-there" && comment.targetId === story.id).length;
            const reactions = library.reactions.filter((reaction) => reaction.targetType === "out-there" && reaction.targetId === story.id).length;
            return (
              <tr key={story.id} className="border-t border-ink/10 align-top">
                <td className="px-3 py-4">
                  <p className="text-lg text-ink">{story.title}</p>
                  <p className="text-sm text-ink-soft">
                    {story.hidden ? "Hidden" : "Visible"}
                    {story.pinned ? " · Pinned" : ""}
                    {story.featured ? " · Featured" : ""}
                  </p>
                  <div className="mt-3">
                    <ModeratePost kind="story" id={story.id} title={story.title} authorId={story.authorId} flags={story} />
                  </div>
                </td>
                <td className="px-3 py-4">{names.get(story.authorId) ?? "Someone"}</td>
                <td className="px-3 py-4">{formatDate(story.createdAt)}</td>
                <td className="px-3 py-4">{category ? categoryLabel(category) : "Open"}</td>
                <td className="px-3 py-4">{comments}</td>
                <td className="px-3 py-4">{reactions}</td>
              </tr>
            );
          })}
        </tbody>
      </QuietTable>

      <div className="mt-14 grid gap-10 lg:grid-cols-2">
        <section>
          <h2 className="font-serif text-3xl">Experiences people responded to</h2>
          <ul className="mt-4 space-y-3">
            {insight.popularStories.map((item) => (
              <li key={item.story.id}>
                {item.story.title}
                <span className="mt-1 block text-sm text-ink-soft">
                  {item.responses} {item.responses === 1 ? "response" : "responses"}
                </span>
              </li>
            ))}
          </ul>
        </section>
        <section>
          <h2 className="font-serif text-3xl">Common themes</h2>
          <div className="mt-4">
            <ShareList rows={insight.themes} empty="No themes yet." />
          </div>
        </section>
        <section>
          <h2 className="font-serif text-3xl">Recently shared locations</h2>
          <ul className="mt-4 space-y-2">
            {insight.locations.map((item) => (
              <li key={item.location}>
                {item.location}
                <span className="text-ink-soft"> · {formatDate(item.when)}</span>
              </li>
            ))}
          </ul>
        </section>
        <section>
          <h2 className="font-serif text-3xl">Frequently mentioned activities</h2>
          <ul className="mt-4 space-y-2">
            {insight.activities.length === 0 ? <li className="text-ink-soft">None yet.</li> : null}
            {insight.activities.map((item) => (
              <li key={item.id}>
                {item.label}
                <span className="text-ink-soft"> · {item.count}</span>
              </li>
            ))}
          </ul>
        </section>
      </div>
    </>
  );
}
