import type { Metadata } from "next";
import { OnboardingFlow } from "@/components/gosolo/onboarding";

export const metadata: Metadata = { title: "Welcome" };

export default function Page() {
  return <OnboardingFlow />;
}
