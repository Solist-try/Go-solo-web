"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import type { ReactNode } from "react";
import { fieldClass, pill } from "@/components/gosolo/pieces";
import { cn } from "@/lib/utils";
import type { Profile } from "@/lib/types";
import type { ShareRow } from "@/lib/admin/insights";

const links = [
  ["/admin", "Dashboard"],
  ["/admin/members", "Members"],
  ["/admin/seeds", "Seeds"],
  ["/admin/same", "SAME"],
  ["/admin/out-there", "Out There"],
  ["/admin/campfire", "Campfire"],
  ["/admin/waypoints", "Waypoints"],
  ["/admin/content", "Content"],
  ["/admin/insights", "Insights"],
  ["/admin/reports", "Reports"],
  ["/admin/settings", "Settings"],
] as const;

const tones = ["bg-sage", "bg-clay", "bg-gold", "bg-mist", "bg-white/80"] as const;

export function AdminShell({ children }: { children: ReactNode }) {
  const pathname = usePathname();
  return (
    <div className="mx-auto grid w-full max-w-6xl gap-8 px-5 py-10 sm:px-8 lg:grid-cols-[190px_minmax(0,1fr)] lg:gap-12 lg:py-14">
      <nav aria-label="Steward" className="flex gap-2 overflow-x-auto pb-1 lg:flex-col lg:overflow-visible lg:pb-0">
        {links.map(([href, label]) => {
          const active = href === "/admin" ? pathname === href : pathname === href || pathname.startsWith(`${href}/`);
          return (
            <Link
              key={href}
              href={href}
              aria-current={active ? "page" : undefined}
              className={cn(
                "shrink-0 rounded-full px-4 py-2 text-sm text-ink-soft hover:text-ink",
                active && "bg-white text-ink shadow-soft",
              )}
            >
              {label}
            </Link>
          );
        })}
      </nav>
      <div className="min-w-0">{children}</div>
    </div>
  );
}

export function DeskIntro({ eyebrow, title, children }: { eyebrow?: string; title: string; children?: ReactNode }) {
  return (
    <header className="max-w-3xl">
      {eyebrow ? <p className="text-sm text-ink-soft">{eyebrow}</p> : null}
      <h1 className="mt-3 font-serif text-5xl leading-[1.05] tracking-tight text-balance text-ink">{title}</h1>
      {children ? <div className="mt-4 max-w-2xl text-lg leading-relaxed text-ink-soft">{children}</div> : null}
    </header>
  );
}

export function Stat({
  label,
  value,
  detail,
  index = 0,
}: {
  label: string;
  value: string | number;
  detail: string;
  index?: number;
}) {
  return (
    <section className={cn("rounded-[28px] p-6 sm:p-7", tones[index % tones.length])}>
      <h2 className="text-sm text-ink-soft">{label}</h2>
      <p className="mt-4 font-serif text-5xl tracking-tight text-ink">{value}</p>
      <p className="mt-3 text-base leading-relaxed text-ink">{detail}</p>
    </section>
  );
}

export function ShareList({ rows, empty }: { rows: ShareRow[]; empty: string }) {
  if (rows.length === 0) return <p className="text-ink-soft">{empty}</p>;
  const max = Math.max(...rows.map((row) => row.count), 1);
  return (
    <ul className="space-y-5">
      {rows.map((row) => (
        <li key={row.id}>
          <div className="flex items-baseline justify-between gap-4">
            <span className="text-lg text-ink">{row.label}</span>
            <span className="text-sm text-ink-soft">
              {row.percent}% · {row.count}
            </span>
          </div>
          <div className="mt-2 h-2 rounded-full bg-white/80">
            <div className="h-2 rounded-full bg-ink/70" style={{ width: `${Math.max(8, (row.count / max) * 100)}%` }} />
          </div>
        </li>
      ))}
    </ul>
  );
}

export function QuietTable({ children }: { children: ReactNode }) {
  return (
    <div className="mt-8 overflow-x-auto">
      <table className="w-full min-w-[760px] border-separate border-spacing-0 text-left">{children}</table>
    </div>
  );
}

export function memberEmail(profile: Profile) {
  return profile.email || `${profile.id}@preview.gosolo`;
}

export function downloadJson(filename: string, value: unknown) {
  const blob = new Blob([JSON.stringify(value, null, 2)], { type: "application/json" });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.download = filename;
  link.click();
  URL.revokeObjectURL(url);
}

export function TextField({
  label,
  value,
  onChange,
  area = false,
}: {
  label: string;
  value: string;
  onChange: (value: string) => void;
  area?: boolean;
}) {
  return (
    <label className="block space-y-2">
      <span className="text-sm text-ink">{label}</span>
      {area ? (
        <textarea
          className="min-h-28 w-full rounded-[24px] border border-input bg-white/80 px-4 py-3 text-base leading-relaxed"
          value={value}
          onChange={(event) => onChange(event.target.value)}
        />
      ) : (
        <input className={fieldClass} value={value} onChange={(event) => onChange(event.target.value)} />
      )}
    </label>
  );
}

export function Choice({
  label,
  value,
  options,
  onChange,
}: {
  label: string;
  value: string;
  options: { id: string; label: string }[];
  onChange: (value: string) => void;
}) {
  return (
    <label className="block space-y-2">
      <span className="text-sm text-ink">{label}</span>
      <select
        className={fieldClass}
        value={value}
        onChange={(event) => onChange(event.target.value)}
      >
        {options.map((option) => (
          <option key={option.id} value={option.id}>
            {option.label}
          </option>
        ))}
      </select>
    </label>
  );
}

export const deskButton = pill;
