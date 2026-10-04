"use client";

import { deskButton } from "@/components/gosolo/admin/ui";
import { Button } from "@/components/ui/button";
import { note, type PostModeration } from "@/lib/admin/state";
import { useGoSolo } from "@/lib/gosolo";

export function ModeratePost({
  kind,
  id,
  title,
  authorId,
  flags,
}: {
  kind: "story" | "campfire";
  id: string;
  title: string;
  authorId: string;
  flags: PostModeration;
}) {
  const { updateDesk, notifyMember, desk, library } = useGoSolo();
  const key = kind === "story" ? "storyModeration" : "campfireModeration";
  const author = library.profiles.find((profile) => profile.id === authorId);

  function patch(next: PostModeration, message: string) {
    updateDesk((admin) =>
      note(
        {
          ...admin,
          [key]: { ...admin[key], [id]: { ...admin[key][id], ...next } },
        },
        message,
      ),
    );
  }

  return (
    <div className="flex flex-wrap gap-2">
      <Button type="button" variant="outline" className={`${deskButton} bg-transparent`} onClick={() => patch({ featured: !flags.featured }, `${flags.featured ? "Unfeatured" : "Featured"} “${title}”.`)}>
        {flags.featured ? "Unfeature" : "Feature"}
      </Button>
      <Button type="button" variant="outline" className={`${deskButton} bg-transparent`} onClick={() => patch({ pinned: !flags.pinned }, `${flags.pinned ? "Unpinned" : "Pinned"} “${title}”.`)}>
        {flags.pinned ? "Unpin" : "Pin"}
      </Button>
      {kind === "campfire" ? (
        <Button type="button" variant="outline" className={`${deskButton} bg-transparent`} onClick={() => patch({ locked: !flags.locked }, `${flags.locked ? "Opened" : "Rested"} “${title}”.`)}>
          {flags.locked ? "Unlock" : "Lock"}
        </Button>
      ) : null}
      <Button type="button" variant="outline" className={`${deskButton} bg-transparent`} onClick={() => patch({ hidden: !flags.hidden }, `${flags.hidden ? "Showed" : "Hid"} “${title}”.`)}>
        {flags.hidden ? "Show" : "Hide"}
      </Button>
      <Button type="button" variant="outline" className={`${deskButton} bg-transparent`} onClick={() => patch({ removed: true, hidden: true }, `Deleted “${title}”.`)}>
        Delete
      </Button>
      {kind === "campfire" && author ? (
        <>
          <Button
            type="button"
            variant="outline"
            className={`${deskButton} bg-transparent`}
            onClick={() => {
              const message = desk.settings.warnTemplate;
              updateDesk((admin) =>
                note(
                  {
                    ...admin,
                    warnings: [
                      { id: crypto.randomUUID(), userId: author.id, note: message, createdAt: new Date().toISOString() },
                      ...admin.warnings,
                    ],
                  },
                  `Warned ${author.displayName}.`,
                ),
              );
              notifyMember(author.id, "A note from the steward", message);
            }}
          >
            Warn user
          </Button>
          <Button
            type="button"
            variant="outline"
            className={`${deskButton} bg-transparent`}
            onClick={() =>
              updateDesk((admin) =>
                note(
                  { ...admin, memberStatus: { ...admin.memberStatus, [author.id]: "suspended" } },
                  `Paused ${author.displayName}.`,
                ),
              )
            }
          >
            Suspend user
          </Button>
        </>
      ) : null}
    </div>
  );
}
