import type { Metadata } from "next";
import { CampfireIndex } from "@/components/gosolo/campfire";

export const metadata: Metadata = {
  title: "Campfire",
  description: "A shared campfire. Conversations happen here after you've been Out There.",
};

export default function Page() {
  return <CampfireIndex />;
}
