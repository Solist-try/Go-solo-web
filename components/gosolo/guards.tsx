"use client";

import { usePathname, useRouter } from "next/navigation";
import { useEffect, type ReactNode } from "react";
import { CalmState } from "@/components/gosolo/pieces";
import { useGoSolo } from "@/lib/gosolo";

export function RequireMember({ children }: { children: ReactNode }) {
  const { ready, user } = useGoSolo();
  const router = useRouter();
  const pathname = usePathname();

  useEffect(() => {
    if (!ready) return;
    if (!user) {
      router.replace(`/login?next=${encodeURIComponent(pathname)}`);
      return;
    }
    if (!user.emailVerified) {
      router.replace("/verify-email");
      return;
    }
    if (!user.onboardingComplete) {
      router.replace("/onboarding");
    }
  }, [ready, user, router, pathname]);

  if (!ready || !user?.emailVerified || !user.onboardingComplete) {
    return <CalmState label="Opening your door…" />;
  }

  return children;
}
