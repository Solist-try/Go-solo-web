import type { Metadata } from "next";
import { Suspense } from "react";
import { CalmState } from "@/components/gosolo/pieces";
import { StoryForm } from "@/components/gosolo/stories";

export const metadata: Metadata = { title: "Bring a story back" };

export default function Page() {
  return (
    <Suspense fallback={<CalmState label="Preparing a page…" />}>
      <StoryForm />
    </Suspense>
  );
}
