"use client";

import Link from "next/link";
import { DeskIntro, deskButton } from "@/components/gosolo/admin/ui";
import { Button } from "@/components/ui/button";
import { note } from "@/lib/admin/state";
import { formatDate } from "@/lib/format";
import { useGoSolo } from "@/lib/gosolo";

export function ReportsAdmin() {
  const { desk, library, updateDesk } = useGoSolo();
  const reports = [...desk.reports].sort((a, b) => (a.createdAt < b.createdAt ? 1 : -1));

  function targetTitle(type: string, id: string) {
    if (type === "out-there") return library.stories.find((story) => story.id === id)?.title ?? "A story";
    if (type === "campfire") return library.campfire.find((post) => post.id === id)?.title ?? "A conversation";
    return library.profiles.find((profile) => profile.id === id)?.displayName ?? "A member";
  }

  return (
    <>
      <DeskIntro eyebrow="Reports" title="Things asking for a careful look.">
        Resolve what can stay. Dismiss what was a misunderstanding. Hide a post when the room is safer without it.
      </DeskIntro>
      <ul className="mt-8 space-y-4">
        {reports.map((report) => (
          <li key={report.id} className="rounded-[28px] bg-white/80 p-6 shadow-soft">
            <p className="text-sm text-ink-soft">
              {report.status} · {formatDate(report.createdAt)} · {report.targetType}
            </p>
            <h2 className="mt-2 font-serif text-2xl text-ink">{targetTitle(report.targetType, report.targetId)}</h2>
            <p className="mt-3 max-w-2xl leading-relaxed">{report.reason}</p>
            <div className="mt-4 flex flex-wrap gap-2">
              <Button
                type="button"
                className={deskButton}
                onClick={() =>
                  updateDesk((admin) =>
                    note(
                      {
                        ...admin,
                        reports: admin.reports.map((item) => (item.id === report.id ? { ...item, status: "resolved" } : item)),
                      },
                      `Resolved a report on “${targetTitle(report.targetType, report.targetId)}”.`,
                    ),
                  )
                }
              >
                Resolve
              </Button>
              <Button
                type="button"
                variant="outline"
                className={`${deskButton} bg-transparent`}
                onClick={() =>
                  updateDesk((admin) =>
                    note(
                      {
                        ...admin,
                        reports: admin.reports.map((item) => (item.id === report.id ? { ...item, status: "dismissed" } : item)),
                      },
                      `Dismissed a report.`,
                    ),
                  )
                }
              >
                Dismiss
              </Button>
              {report.targetType !== "member" ? (
                <Button
                  type="button"
                  variant="outline"
                  className={`${deskButton} bg-transparent`}
                  onClick={() =>
                    updateDesk((admin) => {
                      const key = report.targetType === "out-there" ? "storyModeration" : "campfireModeration";
                      return note(
                        {
                          ...admin,
                          [key]: {
                            ...admin[key],
                            [report.targetId]: { ...admin[key][report.targetId], hidden: true },
                          },
                          reports: admin.reports.map((item) => (item.id === report.id ? { ...item, status: "resolved" } : item)),
                        },
                        `Hid “${targetTitle(report.targetType, report.targetId)}”.`,
                      );
                    })
                  }
                >
                  Hide
                </Button>
              ) : null}
              <Link href={report.targetType === "campfire" ? "/admin/campfire" : report.targetType === "out-there" ? "/admin/out-there" : `/admin/members/${report.targetId}`} className="inline-flex h-12 items-center px-3 underline decoration-ink/20 underline-offset-4">
                View
              </Link>
            </div>
          </li>
        ))}
      </ul>
    </>
  );
}
