import type { Metadata } from "next";
import { ProfileView } from "@/components/gosolo/profile";

export const metadata: Metadata = { title: "Profile" };

export default function Page() {
  return <ProfileView />;
}
