"use client";

import type { ReactNode } from "react";
import { RequireMember } from "@/components/gosolo/guards";

export default function MemberLayout({ children }: { children: ReactNode }) {
  return <RequireMember>{children}</RequireMember>;
}
