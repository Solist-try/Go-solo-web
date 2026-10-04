import type { Metadata } from "next";
import { Suspense } from "react";
import { CampfireForm } from "@/components/gosolo/campfire";
import { CalmState } from "@/components/gosolo/pieces";

export const metadata: Metadata = { title: "Pull up a chair" };

export default function Page() {
  return (
    <Suspense fallback={<CalmState label="Setting a chair…" />}>
      <CampfireForm />
    </Suspense>
  );
}
