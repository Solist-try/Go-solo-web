"use client";

import { Panel, PrimaryLink } from "@/components/gosolo/pieces";
import { useGoSolo } from "@/lib/gosolo";
import { suggestNext } from "@/lib/suggest";

export function NextStep({ compact = false }: { compact?: boolean }) {
  const { user, world, seeds } = useGoSolo();
  if (!user) return null;
  const step = suggestNext({
    userId: user.id,
    interests: user.interests,
    userSeeds: world.userSeeds,
    stories: world.stories,
    campfire: world.campfire,
    memberships: world.memberships,
    seeds,
  });

  return (
    <Panel tone="gold" className={compact ? "mt-10" : "mt-16"}>
      <p className="text-sm text-ink-soft">One small thing</p>
      <h2 className="mt-3 font-serif text-4xl leading-tight tracking-tight text-ink">{step.title}</h2>
      <p className="mt-4 max-w-2xl text-lg leading-relaxed text-ink">{step.body}</p>
      <div className="mt-8">
        <PrimaryLink href={step.href}>{step.cta}</PrimaryLink>
      </div>
    </Panel>
  );
}
