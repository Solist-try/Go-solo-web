import type { Metadata } from "next";
import { VerifyEmail } from "@/components/gosolo/auth-forms";

export const metadata: Metadata = { title: "Confirm your email" };

export default function Page() {
  return <VerifyEmail />;
}
