"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";

export function SiteFooter() {
  const pathname = usePathname();
  const home = pathname === "/";

  return (
    <footer className="mt-auto border-t border-ink/5">
      <div className="mx-auto max-w-6xl px-5 py-16 sm:px-8 sm:py-24">
        {home ? (
          <div id="manifesto" className="max-w-3xl">
            <p className="text-sm text-ink-soft">Go Solo. Not Alone.</p>
            <div className="mt-6 space-y-6 font-serif text-3xl leading-snug tracking-tight text-ink sm:text-4xl">
              <p>Go Solo helps people build bigger lives while living independently.</p>
              <p>Living alone is not the problem. Living on hold is.</p>
              <p>You can live alone without being alone.</p>
            </div>
          </div>
        ) : (
          <p className="max-w-xl font-serif text-3xl leading-snug tracking-tight text-ink">
            Go Solo. Not Alone.
          </p>
        )}
        <div className="mt-12 flex flex-col gap-6 text-sm text-ink-soft sm:flex-row sm:items-end sm:justify-between">
          <p>You can live alone without being alone.</p>
          <nav aria-label="Footer" className="flex flex-wrap gap-x-5 gap-y-2">
            <Link href="/seeds" className="hover:text-ink">
              Seeds
            </Link>
            <Link href="/waypoints" className="hover:text-ink">
              Waypoints
            </Link>
            <Link href="/login" className="hover:text-ink">
              Log in
            </Link>
            <Link href="/register" className="hover:text-ink">
              Join
            </Link>
          </nav>
        </div>
      </div>
    </footer>
  );
}
