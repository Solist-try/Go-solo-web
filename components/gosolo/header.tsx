"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useMemo, useState } from "react";
import { Menu } from "lucide-react";
import { pill } from "@/components/gosolo/pieces";
import { Button } from "@/components/ui/button";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from "@/components/ui/sheet";
import { useGoSolo } from "@/lib/gosolo";
import { cn } from "@/lib/utils";

const memberLinks = [
  ["/dashboard", "Dashboard"],
  ["/seeds", "Seeds"],
  ["/out-there", "Out There"],
  ["/campfire", "Campfire"],
  ["/waypoints", "Waypoints"],
  ["/profile", "Profile"],
] as const;

function Wordmark() {
  return (
    <Link href="/" className="font-serif text-2xl tracking-tight text-ink">
      Go Solo
    </Link>
  );
}

function isActive(pathname: string, href: string) {
  if (href === "/dashboard" || href === "/profile") {
    return pathname === href || pathname.startsWith(`${href}/`);
  }
  return pathname === href || pathname.startsWith(`${href}/`);
}

export function SiteHeader() {
  const pathname = usePathname();
  const { ready, user, world, logout, markNotificationsRead, schemaError } = useGoSolo();
  const [open, setOpen] = useState(false);
  const notes = useMemo(
    () => world.notifications.filter((note) => note.userId === user?.id),
    [world.notifications, user?.id],
  );
  const unread = notes.some((note) => !note.read);

  const links = user?.onboardingComplete
    ? memberLinks
    : [
        ["/seeds", "Seeds"],
        ["/waypoints", "Waypoints"],
      ];

  return (
    <header className="sticky top-0 z-40 border-b border-ink/5 bg-background/90 backdrop-blur-md">
      <div className="mx-auto flex h-20 w-full max-w-6xl items-center justify-between gap-4 px-5 sm:px-8">
        <Wordmark />
        <nav aria-label="Primary" className="hidden items-center gap-1 lg:flex">
          {ready &&
            links.map(([href, label]) => (
              <Link
                key={href}
                href={href}
                aria-current={isActive(pathname, href) ? "page" : undefined}
                className={cn(
                  "rounded-full px-4 py-2 text-base text-ink-soft hover:text-ink",
                  isActive(pathname, href) && "bg-white text-ink shadow-soft",
                )}
              >
                {label}
              </Link>
            ))}
        </nav>
        <div className="flex items-center gap-2">
          {!ready ? <div className="h-12 w-28" /> : null}
          {ready && user?.onboardingComplete ? (
            <>
              <DropdownMenu
                onOpenChange={(next) => {
                  if (next && unread) void markNotificationsRead();
                }}
              >
                <DropdownMenuTrigger asChild>
                  <Button
                    variant="outline"
                    className={cn(pill, "bg-transparent px-4")}
                    aria-label={unread ? "Notes waiting" : "Notes"}
                  >
                    <span className="relative">
                      Notes
                      {unread ? (
                        <span className="absolute -top-1 -right-3 size-2 rounded-full bg-clay ring-2 ring-background" />
                      ) : null}
                    </span>
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-80 rounded-[24px] p-2">
                  {notes.length === 0 ? (
                    <p className="px-3 py-4 text-sm leading-relaxed text-ink-soft">
                      Nothing needs you right now. Go live a little, then come back.
                    </p>
                  ) : (
                    notes.slice(0, 6).map((note) => (
                      <DropdownMenuItem key={note.id} asChild className="rounded-2xl px-3 py-3">
                        <Link href={note.href || "/dashboard"}>
                          <span>
                            <span className="block text-sm text-ink">{note.title}</span>
                            <span className="mt-1 block text-sm leading-relaxed whitespace-normal text-ink-soft">
                              {note.body}
                            </span>
                          </span>
                        </Link>
                      </DropdownMenuItem>
                    ))
                  )}
                </DropdownMenuContent>
              </DropdownMenu>
              <Button asChild variant="ghost" className={cn(pill, "hidden sm:inline-flex")}>
                <Link href="/settings">Settings</Link>
              </Button>
              <Button
                variant="outline"
                className={cn(pill, "hidden bg-transparent sm:inline-flex")}
                onClick={() => void logout()}
              >
                Log out
              </Button>
            </>
          ) : null}
          {ready && user && !user.onboardingComplete ? (
            <>
              <Button asChild className={cn(pill, "hidden sm:inline-flex")}>
                <Link href={user.emailVerified ? "/onboarding" : "/verify-email"}>Continue</Link>
              </Button>
              <Button
                variant="outline"
                className={cn(pill, "hidden bg-transparent sm:inline-flex")}
                onClick={() => void logout()}
              >
                Log out
              </Button>
            </>
          ) : null}
          {ready && !user ? (
            <>
              <Button asChild variant="ghost" className={cn(pill, "hidden sm:inline-flex")}>
                <Link href="/login">Log in</Link>
              </Button>
              <Button asChild className={cn(pill, "hidden sm:inline-flex")}>
                <Link href="/register">Join Go Solo</Link>
              </Button>
            </>
          ) : null}
          <Sheet open={open} onOpenChange={setOpen}>
            <SheetTrigger asChild>
              <Button variant="outline" size="icon" className="size-12 rounded-full lg:hidden" aria-label="Open menu">
                <Menu />
              </Button>
            </SheetTrigger>
            <SheetContent side="right" className="w-full bg-background sm:max-w-sm">
              <SheetHeader>
                <SheetTitle className="font-serif text-3xl">Go Solo</SheetTitle>
              </SheetHeader>
              <nav aria-label="Mobile" className="flex flex-col gap-2 px-4">
                {(user?.onboardingComplete
                  ? [...memberLinks, ["/settings", "Settings"] as const]
                  : user
                    ? [
                        ["/seeds", "Seeds"],
                        ["/waypoints", "Waypoints"],
                        [user.emailVerified ? "/onboarding" : "/verify-email", "Continue"],
                      ]
                    : [
                        ["/seeds", "Seeds"],
                        ["/waypoints", "Waypoints"],
                        ["/login", "Log in"],
                        ["/register", "Join Go Solo"],
                      ]
                ).map(([href, label]) => (
                  <Link
                    key={href}
                    href={href}
                    onClick={() => setOpen(false)}
                    className="rounded-[24px] px-4 py-4 text-2xl font-serif text-ink hover:bg-white"
                  >
                    {label}
                  </Link>
                ))}
                {user ? (
                  <button
                    type="button"
                    className="rounded-[24px] px-4 py-4 text-left text-2xl font-serif text-ink hover:bg-white"
                    onClick={() => {
                      setOpen(false);
                      void logout();
                    }}
                  >
                    Log out
                  </button>
                ) : null}
              </nav>
            </SheetContent>
          </Sheet>
        </div>
      </div>
      {schemaError ? (
        <p className="mx-auto max-w-6xl px-5 pb-4 text-sm text-ink-soft sm:px-8">
          Supabase is connected, but the tables are not ready yet. Run the migration in
          supabase/migrations, then refresh.
        </p>
      ) : null}
    </header>
  );
}
