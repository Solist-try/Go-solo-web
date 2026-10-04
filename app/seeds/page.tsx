import type { Metadata } from "next";
import { SeedIndex } from "@/components/gosolo/seeds";

export const metadata: Metadata = {
  title: "Seeds",
  description: "Small actions that make life bigger. Possibility, not productivity.",
};

export default function Page() {
  return <SeedIndex />;
}
