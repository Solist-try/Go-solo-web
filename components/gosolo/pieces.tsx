import Link from "next/link";
import type { ReactNode } from "react";
import { Button } from "@/components/ui/button";
import { initials } from "@/lib/format";
import type { Profile } from "@/lib/types";
import { cn } from "@/lib/utils";

export const pill = "h-12 rounded-full px-6 text-base";
export const fieldClass =
  "h-12 rounded-[24px] border-input bg-white/80 px-4 text-base shadow-none md:text-base";
export const areaClass =
  "min-h-36 rounded-[24px] border-input bg-white/80 px-4 py-3 text-base leading-relaxed md:text-base";

const tones = ["bg-sage", "bg-clay", "bg-gold", "bg-mist"] as const;

function toneFor(id: string) {
  let hash = 0;
  for (const char of id) hash = (hash + char.charCodeAt(0)) % tones.length;
  return tones[hash] ?? "bg-sage";
}

export function Frame({
  children,
  className,
}: {
  children: ReactNode;
  className?: string;
}) {
  return (
    <div className={cn("mx-auto w-full max-w-6xl px-5 py-14 sm:px-8 sm:py-20", className)}>
      {children}
    </div>
  );
}

export function PageIntro({
  eyebrow,
  title,
  children,
}: {
  eyebrow?: string;
  title: string;
  children?: ReactNode;
}) {
  return (
    <header className="max-w-3xl">
      {eyebrow ? <p className="text-sm text-ink-soft">{eyebrow}</p> : null}
      <h1 className="mt-3 font-serif text-5xl leading-[1.05] tracking-tight text-balance text-ink sm:text-6xl">
        {title}
      </h1>
      {children ? (
        <div className="mt-6 max-w-2xl text-lg leading-relaxed text-ink-soft">{children}</div>
      ) : null}
    </header>
  );
}

export function Panel({
  children,
  className,
  tone = "paper",
  as: Tag = "section",
}: {
  children: ReactNode;
  className?: string;
  tone?: "paper" | "sage" | "clay" | "gold" | "mist";
  as?: "section" | "article" | "div" | "li";
}) {
  const styles = {
    paper: "bg-white/80",
    sage: "bg-sage",
    clay: "bg-clay",
    gold: "bg-gold",
    mist: "bg-mist",
  };
  return (
    <Tag className={cn("rounded-[28px] p-6 shadow-soft sm:p-8", styles[tone], className)}>
      {children}
    </Tag>
  );
}

export function PersonAvatar({
  profile,
  className,
}: {
  profile?: Pick<Profile, "id" | "displayName" | "avatarUrl"> | null;
  className?: string;
}) {
  const name = profile?.displayName || "Member";
  if (profile?.avatarUrl) {
    return (
      // eslint-disable-next-line @next/next/no-img-element
      <img
        src={profile.avatarUrl}
        alt=""
        className={cn("size-12 rounded-full object-cover", className)}
      />
    );
  }
  return (
    <span
      aria-hidden
      className={cn(
        "inline-flex size-12 items-center justify-center rounded-full font-serif text-lg text-ink",
        toneFor(profile?.id || name),
        className,
      )}
    >
      {initials(name)}
    </span>
  );
}

export function AuthorLine({
  profile,
  meta,
  href,
}: {
  profile?: Profile;
  meta?: string;
  href?: string;
}) {
  const content = (
    <>
      <PersonAvatar profile={profile} />
      <span>
        <span className="block text-base text-ink">{profile?.displayName || "A member"}</span>
        {meta ? <span className="block text-sm text-ink-soft">{meta}</span> : null}
      </span>
    </>
  );
  if (!href) return <div className="flex items-center gap-3">{content}</div>;
  return (
    <Link href={href} className="flex items-center gap-3 rounded-full focus-visible:outline-offset-4">
      {content}
    </Link>
  );
}

export function ChoiceGrid({
  label,
  options,
  value,
  onChange,
}: {
  label: string;
  options: readonly { id: string; label: string; hint?: string }[];
  value: string[];
  onChange: (next: string[]) => void;
}) {
  return (
    <div role="group" aria-label={label} className="grid gap-3 sm:grid-cols-2">
      {options.map((option) => {
        const selected = value.includes(option.id);
        return (
          <button
            key={option.id}
            type="button"
            aria-pressed={selected}
            onClick={() =>
              onChange(
                selected ? value.filter((item) => item !== option.id) : [...value, option.id],
              )
            }
            className={cn(
              "min-h-16 rounded-[24px] px-5 py-4 text-left text-base transition",
              selected ? "bg-ink text-background" : "bg-white/75 text-ink hover:bg-white",
            )}
          >
            <span className="block">{option.label}</span>
            {option.hint ? (
              <span className={cn("mt-1 block text-sm", selected ? "text-background/75" : "text-ink-soft")}>
                {option.hint}
              </span>
            ) : null}
          </button>
        );
      })}
    </div>
  );
}

export function QuietSwitch({
  label,
  hint,
  checked,
  onChange,
}: {
  label: string;
  hint?: string;
  checked: boolean;
  onChange: (next: boolean) => void;
}) {
  return (
    <button
      type="button"
      role="switch"
      aria-checked={checked}
      onClick={() => onChange(!checked)}
      className="flex w-full items-start justify-between gap-6 rounded-[24px] bg-white/75 p-5 text-left"
    >
      <span>
        <span className="block text-base text-ink">{label}</span>
        {hint ? <span className="mt-1 block text-sm leading-relaxed text-ink-soft">{hint}</span> : null}
      </span>
      <span className={cn("mt-1 h-7 w-12 shrink-0 rounded-full p-1", checked ? "bg-ink" : "bg-mist")}>
        <span
          className={cn(
            "block size-5 rounded-full bg-background transition-transform",
            checked && "translate-x-5",
          )}
        />
      </span>
    </button>
  );
}

export function CalmState({ label }: { label: string }) {
  return (
    <Frame>
      <p className="font-serif text-3xl text-ink">{label}</p>
    </Frame>
  );
}

export function TextLink({
  href,
  children,
  className,
}: {
  href: string;
  children: ReactNode;
  className?: string;
}) {
  return (
    <Link
      href={href}
      className={cn("underline decoration-ink/20 underline-offset-4 hover:decoration-ink", className)}
    >
      {children}
    </Link>
  );
}

export function PrimaryLink({
  href,
  children,
}: {
  href: string;
  children: ReactNode;
}) {
  return (
    <Button asChild className={pill}>
      <Link href={href}>{children}</Link>
    </Button>
  );
}

export function SecondaryLink({
  href,
  children,
}: {
  href: string;
  children: ReactNode;
}) {
  return (
    <Button asChild variant="outline" className={cn(pill, "bg-transparent")}>
      <Link href={href}>{children}</Link>
    </Button>
  );
}
