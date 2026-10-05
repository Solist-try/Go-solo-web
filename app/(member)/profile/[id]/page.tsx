import type { Metadata } from "next";
import { ProfileView } from "@/components/gosolo/profile";

export const metadata: Metadata = {
  title: "Profile",
  description: "What this person is growing, the help they need, and the help they can offer.",
};

export default async function Page({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  return <ProfileView profileId={id} />;
}
