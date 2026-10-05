import type { Metadata } from "next";
import { AboutPage } from "@/components/gosolo/about";

export const metadata: Metadata = {
  title: "About Go Solo",
  description: "Why Go Solo exists, and a note from Marge Aliaga, the person who started it.",
};

export default function Page() {
  return <AboutPage />;
}
