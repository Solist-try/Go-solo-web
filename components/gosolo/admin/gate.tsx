"use client";

import Link from "next/link";
import { usePathname, useRouter } from "next/navigation";
import { useEffect, type ReactNode } from "react";
import { AdminShell } from "@/components/gosolo/admin/ui";
import { CalmState } from "@/components/gosolo/pieces";
import { useGoSolo } from "@/lib/gosolo";

export function AdminGate({ children }: { children: ReactNode }) {
  const { ready, user } = useGoSolo();
  const router = useRouter();
  const pathname = usePathname();

  useEffect(() => {
    if (!ready || user) return;
    router.replace(`/login?next=${encodeURIComponent(pathname)}`);
  }, [ready, user, router, pathname]);

  if (!ready || !user) return <CalmState label="Opening the desk…" />;
  if (user.role !== "admin") {
    return (
      <div className="mx-auto max-w-xl px-5 py-24">
        <p className="text-sm text-ink-soft">Steward desk</p>
        <h1 className="mt-3 font-serif text-5xl tracking-tight text-ink">This desk is kept by stewards.</h1>
        <p className="mt-4 text-lg leading-relaxed text-ink-soft">
          Your member home is still here. The reading of the room stays with the people asked to tend it.
        </p>
        <Link href="/dashboard" className="mt-8 inline-block text-lg underline decoration-ink/20 underline-offset-4">
          Return home
        </Link>
      </div>
    );
  }
  return <AdminShell>{children}</AdminShell>;
}
