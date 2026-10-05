"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useGoSolo } from "@/lib/gosolo";

export function SiteFooter() {
  const pathname = usePathname();
  const home = pathname === "/";
  const { content } = useGoSolo();

  return (
    <footer className="mt-auto border-t border-ink/5">
      <div className="mx-auto max-w-6xl px-5 py-16 sm:px-8 sm:py-24">
        <p className="font-serif text-[clamp(1.45rem,5vw,3rem)] leading-tight tracking-tight whitespace-nowrap text-ink">{content.heroTagline}</p>
        <div className="mt-12 flex flex-col gap-6 text-sm text-ink-soft sm:flex-row sm:items-end sm:justify-between">
          {home ? null : <p>A home base you can leave, and return to.</p>}
          <nav aria-label="Footer" className="flex flex-wrap gap-x-5 gap-y-2">
            {[
              ["/about", "About"],
              ["/seeds", "Seeds"],
              ["/out-there", "Out There"],
              ["/campfire", "Campfire"],
              ["/waypoints", "Waypoints"],
              ["/reading-room", "Reading Room"],
              ["/contact", "Contact"],
              ["/register", "Join"],
              ["/login", "Log in"],
            ].map(([href, label]) => (
              <Link key={href} href={href} className="hover:text-ink">
                {label}
              </Link>
            ))}
          </nav>
        </div>
      </div>
    </footer>
  );
}
