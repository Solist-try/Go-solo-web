import type { Metadata } from "next";
import { SettingsScreen } from "@/components/gosolo/settings";

export const metadata: Metadata = { title: "Settings" };

export default function Page() {
  return <SettingsScreen />;
}
