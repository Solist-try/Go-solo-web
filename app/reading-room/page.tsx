import type { Metadata } from "next";
import { ReadingRoom } from "@/components/gosolo/reading-room";

export const metadata: Metadata = {
  title: "Reading Room",
  description: "Collected ideas, reflections and resources for independent living.",
};

export default function Page() {
  return <ReadingRoom />;
}
