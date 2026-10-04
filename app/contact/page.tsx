import type { Metadata } from "next";
import { ContactPage } from "@/components/gosolo/contact";

export const metadata: Metadata = {
  title: "Contact",
  description: "Write to the person who tends Go Solo.",
};

export default function Page() {
  return <ContactPage />;
}
