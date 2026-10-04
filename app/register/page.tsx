import type { Metadata } from "next";
import { RegisterForm } from "@/components/gosolo/auth-forms";

export const metadata: Metadata = { title: "Join Go Solo" };

export default function Page() {
  return <RegisterForm />;
}
