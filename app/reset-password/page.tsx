import type { Metadata } from "next";
import { Suspense } from "react";
import { ResetForm } from "@/components/gosolo/auth-forms";
import { CalmState } from "@/components/gosolo/pieces";

export const metadata: Metadata = { title: "Choose a new password" };

export default function Page() {
  return (
    <Suspense fallback={<CalmState label="Preparing a quiet reset…" />}>
      <ResetForm />
    </Suspense>
  );
}
