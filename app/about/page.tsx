import type { Metadata } from "next";
import { AboutPage } from "@/components/gosolo/about";

export const metadata: Metadata = {
  title: "Why Go Solo Exists",
  description: "Why Go Solo exists, what it believes, and the person who tends it.",
};

export default function Page() {
  return <AboutPage />;
}
