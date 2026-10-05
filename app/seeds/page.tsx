import type { Metadata } from "next";
import { SeedIndex } from "@/components/gosolo/seeds";

export const metadata: Metadata = {
  title: "Seeds",
  description: "Tiny futures you can plant now. Growth, connection and possibility.",
};

export default function Page() {
  return <SeedIndex />;
}
