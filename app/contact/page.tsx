import type { Metadata } from "next";
import { ContactPage } from "@/components/gosolo/contact";

export const metadata: Metadata = {
  title: "Contact",
  description: "Write to Marge Aliaga.",
};

export default function Page() {
  return <ContactPage />;
}
