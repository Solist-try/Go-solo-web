import type { Metadata } from "next";
import { Suspense } from "react";
import { LoginForm } from "@/components/gosolo/auth-forms";
import { CalmState } from "@/components/gosolo/pieces";

export const metadata: Metadata = { title: "Log in" };

export default function Page() {
  return (
    <Suspense fallback={<CalmState label="Opening the door…" />}>
      <LoginForm />
    </Suspense>
  );
}
