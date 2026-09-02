interface UserAvatarProps {
  name: string;
  avatarUrl?: string | null;
  size?: number;
}

const COLORS = [
  '#f97316', '#ef4444', '#84cc16', '#06b6d4',
  '#8b5cf6', '#ec4899', '#0ea5e9', '#22c55e',
];

function initialsOf(name: string) {
  const parts = name.trim().split(/\s+/);
  const initials = parts.length > 1 ? parts[0][0] + parts[1][0] : name.slice(0, 2);
  return initials.toUpperCase();
}

function colorFor(name: string) {
  let hash = 0;
  for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
  return COLORS[Math.abs(hash) % COLORS.length];
}

/** Real avatar image when one exists; otherwise a deterministic initials SVG.
 *  Never renders an <img> with an empty/missing src. */
export function UserAvatar({ name, avatarUrl, size = 32 }: UserAvatarProps) {
  if (avatarUrl) {
    return (
      <img
        src={avatarUrl}
        alt={name}
        width={size}
        height={size}
        className="rounded-full object-cover"
        style={{ width: size, height: size }}
      />
    );
  }

  const bg = colorFor(name || '?');
  const initials = initialsOf(name || '?');

  return (
    <svg width={size} height={size} viewBox="0 0 32 32" className="rounded-full">
      <circle cx="16" cy="16" r="16" fill={bg} />
      <text
        x="16"
        y="16"
        textAnchor="middle"
        dominantBaseline="central"
        fill="white"
        fontSize="12"
        fontWeight="600"
        fontFamily="ui-sans-serif, system-ui, sans-serif"
      >
        {initials}
      </text>
    </svg>
  );
}
